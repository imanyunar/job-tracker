<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobUrlParserService
{
    /**
     * Known job board domains and their display names.
     */
    protected array $platformMap = [
        'linkedin.com' => 'LinkedIn',
        'jobstreet.co.id' => 'Jobstreet',
        'jobstreet.com' => 'Jobstreet',
        'jobstreet.com.sg' => 'Jobstreet',
        'glints.com' => 'Glints',
        'indeed.com' => 'Indeed',
        'id.indeed.com' => 'Indeed',
        'kalibrr.com' => 'Kalibrr',
        'kalibrr.id' => 'Kalibrr',
        'techinasia.com' => 'Tech in Asia',
        'dealls.com' => 'Dealls',
        'klob.id' => 'Klob',
        'glassdoor.com' => 'Glassdoor',
        'karir.com' => 'Karir.com',
        'disnaker' => 'Disnaker',
        'prosple.com' => 'Prosple',
        'internshala.com' => 'Internshala',
    ];

    /**
     * Parse and extract job information from a given URL.
     */
    public function parse(string $url): array
    {
        $cleanUrl = $this->sanitizeUrl($url);
        $source = $this->detectPlatform($cleanUrl);

        $result = [
            'company_name' => null,
            'position' => null,
            'status' => 'applied',
            'source' => $source,
            'location' => null,
            'job_url' => $cleanUrl,
            'salary_range_min' => null,
            'salary_range_max' => null,
            'notes' => null,
        ];

        try {
            $html = $this->fetchHtml($cleanUrl);
            if (empty($html)) {
                return $result;
            }

            // Layer 1: Schema.org JSON-LD structured data (Best precision)
            $jsonLdData = $this->extractJsonLd($html);
            if ($jsonLdData) {
                $result = array_merge($result, array_filter($jsonLdData, fn($val) => !is_null($val) && $val !== ''));
            }

            // Layer 2: Open Graph and HTML Meta tags
            if (empty($result['company_name']) || empty($result['position'])) {
                $metaData = $this->extractMetaTags($html);
                foreach (['company_name', 'position', 'location', 'notes'] as $key) {
                    if (empty($result[$key]) && !empty($metaData[$key])) {
                        $result[$key] = $metaData[$key];
                    }
                }
            }

            // Layer 3: Title tag heuristic parsing
            if (empty($result['position']) || empty($result['company_name'])) {
                $titleData = $this->extractFromTitle($html);
                if (empty($result['position']) && !empty($titleData['position'])) {
                    $result['position'] = $titleData['position'];
                }
                if (empty($result['company_name']) && !empty($titleData['company_name'])) {
                    $result['company_name'] = $titleData['company_name'];
                }
            }
        } catch (Throwable $e) {
            Log::info('JobUrlParserService notice: ' . $e->getMessage(), ['url' => $cleanUrl]);
        }

        // Clean strings
        foreach (['company_name', 'position', 'location', 'source'] as $field) {
            if (!empty($result[$field])) {
                $result[$field] = $this->cleanString($result[$field]);
            }
        }

        return $result;
    }

    /**
     * Clean and strip tracking query parameters (utm_*, refId, etc.)
     */
    protected function sanitizeUrl(string $url): string
    {
        $url = trim($url);
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return $url;
        }

        $query = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $query);
            // Drop marketing & tracking parameters
            $ignoreKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'refId', 'trackingId', 'trk', 'tracking_id', 'originalSubdomain'];
            foreach ($ignoreKeys as $k) {
                unset($query[$k]);
            }
        }

        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'];
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '';
        $queryString = !empty($query) ? '?' . http_build_query($query) : '';

        return "{$scheme}://{$host}{$port}{$path}{$queryString}";
    }

    /**
     * Detect job board platform name from URL host.
     */
    protected function detectPlatform(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return null;
        }

        $host = strtolower($host);
        foreach ($this->platformMap as $domain => $name) {
            if (str_contains($host, $domain)) {
                return $name;
            }
        }

        // Format clean domain name (e.g. careers.google.com -> Google)
        $parts = explode('.', str_replace('www.', '', $host));
        if (count($parts) >= 2) {
            return ucfirst($parts[0] === 'careers' || $parts[0] === 'jobs' ? $parts[1] : $parts[0]);
        }

        return null;
    }

    /**
     * Fetch HTML from the target URL with realistic browser headers.
     */
    protected function fetchHtml(string $url): string
    {
        $client = new Client([
            'timeout' => 6.0,
            'connect_timeout' => 4.0,
            'verify' => app()->environment('local') ? false : true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control' => 'no-cache',
            ],
            'http_errors' => false,
        ]);

        $response = $client->get($url);
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
            return (string) $response->getBody();
        }

        return '';
    }

    /**
     * Extract schema.org JobPosting JSON-LD if available.
     */
    protected function extractJsonLd(string $html): ?array
    {
        if (!preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $jsonString) {
            $data = json_decode(trim($jsonString), true);
            if (!$data) {
                continue;
            }

            // Support both direct object and @graph array
            $items = isset($data['@graph']) && is_array($data['@graph']) ? $data['@graph'] : [$data];

            foreach ($items as $item) {
                $type = $item['@type'] ?? '';
                if (is_string($type) && strtolower($type) === 'jobposting') {
                    return $this->mapJobPostingData($item);
                }
            }
        }

        return null;
    }

    /**
     * Map JSON-LD JobPosting schema to application fields.
     */
    protected function mapJobPostingData(array $item): array
    {
        $company = null;
        if (isset($item['hiringOrganization'])) {
            if (is_string($item['hiringOrganization'])) {
                $company = $item['hiringOrganization'];
            } elseif (isset($item['hiringOrganization']['name'])) {
                $company = $item['hiringOrganization']['name'];
            }
        }

        $location = null;
        if (isset($item['jobLocation'])) {
            $loc = $item['jobLocation'];
            if (isset($loc['address'])) {
                $addr = $loc['address'];
                if (is_string($addr)) {
                    $location = $addr;
                } elseif (is_array($addr)) {
                    $locParts = array_filter([
                        $addr['addressLocality'] ?? null,
                        $addr['addressRegion'] ?? null,
                        $addr['addressCountry'] ?? null,
                    ]);
                    $location = implode(', ', $locParts);
                }
            }
        }

        // Location type (Remote / Hybrid)
        if (isset($item['jobLocationType']) && strtolower($item['jobLocationType']) === 'telecommute') {
            $location = $location ? "{$location} (Remote)" : 'Remote';
        }

        // Salary range
        $salaryMin = null;
        $salaryMax = null;
        if (isset($item['baseSalary']['value'])) {
            $val = $item['baseSalary']['value'];
            if (is_numeric($val)) {
                $salaryMin = (float) $val;
            } elseif (is_array($val)) {
                $salaryMin = isset($val['minValue']) ? (float) $val['minValue'] : null;
                $salaryMax = isset($val['maxValue']) ? (float) $val['maxValue'] : null;
                if (!$salaryMin && isset($val['value'])) {
                    $salaryMin = (float) $val['value'];
                }
            }
        }

        $notes = null;
        if (!empty($item['description'])) {
            // Strip HTML tags and summarize
            $cleanDesc = trim(strip_tags($item['description']));
            $cleanDesc = preg_replace('/\s+/', ' ', $cleanDesc);
            $notes = mb_substr($cleanDesc, 0, 300) . (mb_strlen($cleanDesc) > 300 ? '...' : '');
        }

        return [
            'position' => $item['title'] ?? null,
            'company_name' => $company,
            'location' => $location,
            'salary_range_min' => $salaryMin,
            'salary_range_max' => $salaryMax,
            'notes' => $notes,
        ];
    }

    /**
     * Extract data from OpenGraph meta tags and standard meta descriptions.
     */
    protected function extractMetaTags(string $html): array
    {
        $tags = [];
        if (preg_match_all('/<meta[^>]+(?:name|property)=["\']([^"\']+)["\'][^>]+content=["\']([^"\']*)["\']/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $tags[strtolower($m[1])] = $m[2];
            }
        }
        // Also support content before property
        if (preg_match_all('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+(?:name|property)=["\']([^"\']+)["\']/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $tags[strtolower($m[2])] = $m[1];
            }
        }

        $title = $tags['og:title'] ?? ($tags['twitter:title'] ?? null);
        $description = $tags['og:description'] ?? ($tags['description'] ?? null);

        $parsed = [
            'position' => null,
            'company_name' => null,
            'location' => null,
            'notes' => null,
        ];

        if ($title) {
            $parsedTitle = $this->decomposeJobTitle($title);
            $parsed['position'] = $parsedTitle['position'];
            $parsed['company_name'] = $parsedTitle['company_name'];
        }

        if ($description) {
            $cleanDesc = trim(strip_tags($description));
            $cleanDesc = preg_replace('/\s+/', ' ', $cleanDesc);
            $parsed['notes'] = mb_substr($cleanDesc, 0, 300) . (mb_strlen($cleanDesc) > 300 ? '...' : '');
        }

        return $parsed;
    }

    /**
     * Extract title from <title> tag.
     */
    protected function extractFromTitle(string $html): array
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $title = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            return $this->decomposeJobTitle($title);
        }

        return ['position' => null, 'company_name' => null];
    }

    /**
     * Decompose job titles like:
     * - "Frontend Engineer di PT Shopee Indonesia | Jobstreet"
     * - "Senior Software Engineer - GoTo Financial | LinkedIn"
     * - "PT Bank Central Asia hiring Data Analyst in Jakarta"
     */
    protected function decomposeJobTitle(string $rawTitle): array
    {
        $title = trim(html_entity_decode($rawTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        // Strip common suffixes like "| LinkedIn", "- Jobstreet", "• Glints", etc.
        $title = preg_replace('/\s*[\|\-\•\–]\s*(LinkedIn|JobStreet|Glints|Indeed|Kalibrr|Glassdoor|Tech in Asia).*$/i', '', $title);

        $position = null;
        $company = null;

        // Pattern: "Company hiring Position in Location" (LinkedIn format)
        if (preg_match('/^(.*?)\s+hiring\s+(.*?)(?:\s+in\s+.*)?$/i', $title, $m)) {
            $company = trim($m[1]);
            $position = trim($m[2]);
        }
        // Pattern: "Position at Company"
        elseif (preg_match('/^(.*?)\s+at\s+(.*?)$/i', $title, $m)) {
            $position = trim($m[1]);
            $company = trim($m[2]);
        }
        // Pattern: "Position di Company" (Indonesian format)
        elseif (preg_match('/^(.*?)\s+di\s+(.*?)$/i', $title, $m)) {
            $position = trim($m[1]);
            $company = trim($m[2]);
        }
        // Pattern: "Position - Company" or "Company - Position"
        elseif (preg_match('/^(.*?)\s*[\-\–]\s*(.*?)$/i', $title, $m)) {
            $left = trim($m[1]);
            $right = trim($m[2]);
            // If left looks like a known company prefix (PT, CV) or right looks like role (Engineer, Developer, Manager)
            if (preg_match('/^(PT|CV|PT\.)\b/i', $left)) {
                $company = $left;
                $position = $right;
            } else {
                $position = $left;
                $company = $right;
            }
        } else {
            $position = $title;
        }

        return [
            'position' => $position,
            'company_name' => $company,
        ];
    }

    /**
     * Clean and strip junk prefixes or special characters.
     */
    protected function cleanString(?string $str): ?string
    {
        if (!$str) {
            return null;
        }

        $str = html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $str = preg_replace('/\s+/', ' ', $str);
        $str = trim($str, " \t\n\r\0\x0B-–|•");

        return $str ?: null;
    }
}
