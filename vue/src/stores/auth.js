import {ref, computed, inject} from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'
import { useToast } from '@/components/ui/toast/use-toast';
import { ToastAction } from '@/components/ui/toast';

import avatarNoneAssetURL from '@/assets/avatar-none.png'
import router from "@/router/index.js";
import {useRouter} from "vue-router";

export const useAuthStore = defineStore('auth', () => {
  const storeError = useErrorStore();
  const user = ref(null);
  const token = ref('');
  const { toast } = useToast();
  const notifications = ref(true);
  const socket = inject('socket');

  let intervalToRefreshToken = null;

  const userId = computed(() => {
    return user.value ? user.value.id : ''
  })

  const isLoggedIn = computed(() => {
    return user.value?.id !== 'guest' && user.value !== null;
  });

  const userName = computed(() => {
    return user.value ? user.value.name : '';
  });

  const userFirstLastName = computed(() => {
    const names = userName.value.trim().split(' ')
    const firstName = names[0] ?? ''
    const lastName = names.length > 1 ? names[names.length - 1] : ''
    return (firstName + ' ' + lastName).trim()
  })

  const userEmail = computed(() => {
    return user.value ? user.value.email : ''
  })

  const userType = computed(() => {
    return user.value ? user.value.type : ''
  })

  const userGender = computed(() => {
    return user.value ? user.value.gender : ''
  })

  const userBrainCoins = computed(() => {
    return user.value ? user.value.brainCoinsBalance : ''
  })

  const userPhotoUrl = computed(() => {
    return user.value ? user.value.photo_filename : avatarNoneAssetURL
  })
  const getFirstLastName = (fullName) => {
    if (!fullName || typeof fullName !== 'string') {
      return 'Unknown Player'; // Fallback for undefined, null, or non-string values
    }

    const names = fullName.trim().split(' ');
    const firstName = names[0] ?? '';
    const lastName = names.length > 1 ? names[names.length - 1] : '';

    return (firstName + ' ' + lastName).trim();
  };


  const deleteAccount = async () => {
    storeError.resetMessages()
    try {
      await axios.delete('users/me')
      clearUser()
      await router.push({name: 'login'})
      return true
    } catch (e) {
      clearUser()
      storeError.setErrorMessages(
          e.response.data.message,
          e.response.data.errors,
          e.response.status,
          'Delete Account Error!'
      )
      toast({
        description: e.response.data.message,
        variant:"destructive",
      })
      return false
    }
  }

  // Clear user and reset token refresh
  const clearUser = () => {
    resetIntervalToRefreshToken(); // Limpar intervalo de refresh do token
    user.value = null;
    token.value = '';
    localStorage.removeItem('token')
    axios.defaults.headers.common['Authorization'] = '';
  };

  const login = async (credentials) => {
    storeError.resetMessages()
    try {
      const responseLogin = await axios.post('auth/login', credentials)
      token.value = responseLogin.data.token
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      const responseUser = await axios.get('users/me')
      user.value = responseUser.data.data
      socket.emit('login', user.value)
      repeatRefreshToken()
      await router.push({name: 'dashboard'})
      return user.value
    } catch (e) {
      clearUser()
      storeError.setErrorMessages(
        e.response.data.message,
        e.response.data.errors,
        e.response.status,
        'Authentication Error!'
      )
      toast({
        description: e.response.data.message,
        variant:"destructive",
      })

      return false
    }
  }

  const logout = async () => {
    storeError.resetMessages()
    try {
      if(user.value.id === 'guest') {
        localStorage.remove('anonymousUser');
      }else{
        await axios.post('auth/logout');
        socket.emit('logout', user.value);
        clearUser(); // Limpa os dados do usuário e do token
      }
    } catch (e) {
      clearUser()
      storeError.setErrorMessages(
        e.response.data.message,
        [],
        e.response.status,
        'Authentication Error!'
      )
      return false
    }
  }

  // Refresh token function
  const refreshToken = async () => {
    try {
      const response = await axios.post('auth/refreshtoken');
      token.value = response.data.token;
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    } catch (error) {
        clearUser()
        storeError.setErrorMessages(
            error.response.data.message,
            error.response.data.errors,
            error.response.status,
            'Token Refresh Error!'
        )
    }
  };

  const resetIntervalToRefreshToken = () => {
    if (intervalToRefreshToken) {
      clearInterval(intervalToRefreshToken)
    }
    intervalToRefreshToken = null
  }

  const repeatRefreshToken = () => {
    if (intervalToRefreshToken) {
      clearInterval(intervalToRefreshToken)
    }
    intervalToRefreshToken = setInterval(
      async () => {
        try {
          const response = await axios.post('auth/refreshtoken')
          token.value = response.data.token
          axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
          return true
        } catch (e) {
          clearUser()
          storeError.setErrorMessages(
            e.response.data.message,
            e.response.data.errors,
            e.response.status,
            'Authentication Error!'
          )
          return false
        }
      },
      1000 * 60 * 110
    )
    return intervalToRefreshToken
  }

  const restoreToken = async function () {
    let storedToken = localStorage.getItem('token')
    if (storedToken) {
      try {
        token.value = storedToken
        axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
        const responseUser = await axios.get('users/me')
        user.value = responseUser.data.data
        socket.emit('login', user.value)
        repeatRefreshToken()
        return true
      } catch {
        clearUser()
        storeError.setErrorMessages(
          'Invalid token',
          [],
          401,
          'Authentication');
        return false
      }
    }
    return false
  }

  const setUser = (userData) => {
    user.value = userData;
  }

  // Login anônimo local
  const anonymousLogin = () => {
    user.value = {
      id: 'guest', // Gerar um ID único localmente
      name: 'Guest User',
      email: 'guest@example.com',
      brain_coins_balance: 0, // Definir saldo inicial, se aplicável
      isAnonymous: true, // Identificar que é um usuário anônimo
    };
    token.value = null; // Sem token porque é local
    try{
      localStorage.setItem('anonymousUser', JSON.stringify(user.value)); // Salvar no localStorage para persistência
    }catch (e) {
      storeError('Error saving anonymous user in local storage', [], 500, 'Local Storage Error');
    }
  };

  const isAdmin = computed(() => {
      return user.value ? user.value.type === 'A' : false;
  });

  return {
    user,
    userName,
    userFirstLastName,
    userEmail,
    userType,
    userGender,
    userPhotoUrl,
    userBrainCoins,
    userId,
    isLoggedIn,
    notifications,
    isAdmin,
    getFirstLastName,
    login,
    deleteAccount,
    logout,
    refreshToken,
    anonymousLogin,
    setUser,
    restoreToken,
  }
})
