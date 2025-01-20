<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" >
            @csrf

            <div>
                <x-label for="email" value="{{ __('Correo') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Contraseña') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Recordarme') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Olvido su contraseña?') }}
                    </a>
                @endif

                <x-button class="ms-4">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>

        <!--  OR -->
         <div class="my-6 text-center text-sm text-gray-600 flex items-center">
            <hr class="flex-grow border-t border-gray-300">
            <span class="mx-3 text-gray-600 font-medium">OR</span>
            <hr class="flex-grow border-t border-gray-300">
        </div>

         <!-- Google and Microsoft -->
         <div class="mb-6 mt-4">
            <button 
                id="google-login-button"
                class="flex items-center justify-center w-full px-4 py-2 mb-3 bg-white text-gray-800 border border-gray-300 rounded-lg shadow hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRvUEPcQkCb9DNjiN5j_uL51VMehMlc6ye5AQ&s" alt="Google" class="w-5 h-5 mr-2">
                {{ __('Continuar con Google') }}
            </button>

            <button
                class="flex items-center justify-center w-full px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-lg shadow hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                <img src="https://foroalfa.org/imagenes/ilustraciones/1296.jpg" alt="Microsoft" class="w-5 h-5 mr-2">  <!-- Imagen de Microsoft -->
                {{ __('Continuar con Microsoft') }}
            </button>
        </div>

        <div>
            <p class="mt-5 text-center text-sm">
                {{ __('No tienes una cuenta?') }}
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                    {{ __('Registrarme') }}
                </a>
            </p>
        </div>
    </x-authentication-card>
</x-guest-layout>

<script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.17.1/firebase-auth.js"></script>
<script type="module" src="{{ asset('js/firebase-auth.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
