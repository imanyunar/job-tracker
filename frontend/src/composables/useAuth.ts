import { ref, computed } from 'vue';
import type { User, LoginPayload, RegisterPayload } from '../types/job';
import { authApi } from '../services/api';

const user = ref<User | null>(null);
const token = ref<string | null>(authApi.getToken());
const loading = ref<boolean>(false);
const submitting = ref<boolean>(false);
const error = ref<string | null>(null);

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value && !!user.value);

  const checkAuth = async () => {
    const currentToken = authApi.getToken();
    if (!currentToken) {
      user.value = null;
      token.value = null;
      return false;
    }

    loading.value = true;
    try {
      const res = await authApi.getMe();
      if (res.data) {
        user.value = res.data;
        token.value = currentToken;
        return true;
      }
    } catch {
      authApi.removeToken();
      user.value = null;
      token.value = null;
      return false;
    } finally {
      loading.value = false;
    }
    return false;
  };

  const login = async (payload: LoginPayload) => {
    submitting.value = true;
    error.value = null;
    try {
      const res = await authApi.login(payload);
      if (res.data?.user && res.data?.token) {
        user.value = res.data.user;
        token.value = res.data.token;
      }
      return res;
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal masuk. Periksa kembali email dan kata sandi.';
      throw err;
    } finally {
      submitting.value = false;
    }
  };

  const register = async (payload: RegisterPayload) => {
    submitting.value = true;
    error.value = null;
    try {
      const res = await authApi.register(payload);
      if (res.data?.user && res.data?.token) {
        user.value = res.data.user;
        token.value = res.data.token;
      }
      return res;
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Gagal mendaftar akun baru.';
      throw err;
    } finally {
      submitting.value = false;
    }
  };

  const logout = async () => {
    submitting.value = true;
    try {
      await authApi.logout();
    } catch (err) {
      console.warn('Logout request completed with warning:', err);
    } finally {
      user.value = null;
      token.value = null;
      submitting.value = false;
    }
  };

  // Listen to unauthorized event from API interceptor
  if (typeof window !== 'undefined') {
    window.addEventListener('auth:unauthorized', () => {
      user.value = null;
      token.value = null;
    });
  }

  return {
    user,
    token,
    isAuthenticated,
    loading,
    submitting,
    error,
    checkAuth,
    login,
    register,
    logout,
  };
}
