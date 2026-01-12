@extends('layouts.guest')

@section('contents')
<div class="max-w-xl mx-auto mt-16 p-3 bg-white/20 backdrop-blur-md rounded-2xl shadow-2xl transition-all duration-300 text-white">
    <h1 class="text-3xl font-bold text-center mb-8">Instructor Registration</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-6" novalidate>
        @csrf

        {{-- Name Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('First Name')" class="text-white" />
                <x-text-input id="first_name" name="first_name" type="text" placeholder="Juan" class="w-full mt-1 text-white placeholder-white bg-transparent" :value="old('first_name')" required />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2 text-red-400" />
            </div>

            <div>
                <x-input-label for="middle_name" :value="__('Middle Name')" class="text-white" />
                <x-text-input id="middle_name" name="middle_name" type="text" placeholder="(optional)" class="w-full mt-1 text-white placeholder-white bg-transparent" :value="old('middle_name')" />
                <x-input-error :messages="$errors->get('middle_name')" class="mt-2 text-red-400" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="last_name" :value="__('Last Name')" class="text-white" />
                <x-text-input id="last_name" name="last_name" type="text" placeholder="Dela Cruz" class="w-full mt-1 text-white placeholder-white bg-transparent" :value="old('last_name')" required />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2 text-red-400" />
            </div>
        </div>

        {{-- Email Username --}}
        <div>
            <x-input-label for="email" :value="__('Email Username')" class="text-white" />
            <div class="flex rounded-md shadow-sm">
                <x-text-input id="email" name="email" type="text" placeholder="jdelacruz" class="rounded-r-none w-full mt-1 text-white placeholder-white bg-transparent" :value="old('email')" required pattern="^[^@]+$" title="Do not include '@' or domain — just the username." />
                <span class="inline-flex items-center px-3 rounded-r-md bg-white/20 border border-l-0 border-gray-300 mt-1 text-sm text-white">@brokenshire.edu.ph</span>
            </div>
            <p id="email-warning" class="text-sm text-red-400 mt-1 hidden">
                Please enter only your username — do not include '@' or email domain.
            </p>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        {{-- Department --}}
        <div>
            <x-input-label for="department_id" :value="__('Select Department')" class="text-white" />
            <select id="department_id" name="department_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-400 text-white" required>
                <option value="">-- Choose Department --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" data-is-ge="{{ $dept->department_code === 'GE' ? 'true' : 'false' }}">
                        {{ $dept->department_description }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('department_id')" class="mt-2 text-red-400" />
        </div>

        {{-- Course --}}
        <div class="hidden transition-opacity duration-300" id="course-wrapper">
            <x-input-label for="course_id" :value="__('Select Course')" class="text-white" />
            <select id="course_id" name="course_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-400 text-white" required>
                <option value="">-- Choose Course --</option>
            </select>
            <x-input-error :messages="$errors->get('course_id')" class="mt-2 text-red-400" />
        </div>

        {{-- GE Notice --}}
        <div id="ge-notice" class="hidden p-4 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 text-white mt-2">
            <strong class="font-semibold">General Education Instructor:</strong>
            <span class="block sm:inline">You will be able to teach GE subjects across all courses and will be managed by the GE Coordinator.</span>
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-white" />
            <div class="relative">
                <input
                    id="password"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1 pr-12 text-white placeholder-white bg-transparent"
                    type="password"
                    name="password"
                    required
                    placeholder="Min. 8 characters"
                    autocomplete="new-password"
                    oninput="checkPassword(this.value)"
                />
                
                <input
                    id="password-visible"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1 pr-12 text-white placeholder-white bg-transparent"
                    type="text"
                    style="display: none; position: absolute; top: 0; left: 0; width: 100%;"
                    placeholder="Min. 8 characters"
                    tabindex="-1"
                />
                
                <button
                    type="button"
                    id="togglePassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-white/80 hover:text-white focus:outline-none transition-colors duration-200 z-10 hidden"
                >
                    <i class="fas fa-eye-slash" id="toggleIcon"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />

            <div id="password-requirements" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 text-sm text-white">
                <h3 class="md:col-span-2 text-sm font-semibold mb-1">Password Requirements:</h3>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div id="circle-length" class="w-3 h-3 rounded-full bg-gray-300 border transition-all"></div>
                        <span>Minimum 8 chars</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div id="circle-case" class="w-3 h-3 rounded-full bg-gray-300 border transition-all"></div>
                        <span>Upper & lowercase</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div id="circle-number" class="w-3 h-3 rounded-full bg-gray-300 border transition-all"></div>
                        <span>At least 1 number</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div id="circle-special" class="w-3 h-3 rounded-full bg-gray-300 border transition-all"></div>
                        <span>Special character</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-white" />
            <div class="relative">
                <input
                    id="password_confirmation"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1 pr-12 text-white placeholder-white bg-transparent"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                
                <input
                    id="password_confirmation-visible"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1 pr-12 text-white placeholder-white bg-transparent"
                    type="text"
                    style="display: none; position: absolute; top: 0; left: 0; width: 100%;"
                    tabindex="-1"
                />
                
                <button
                    type="button"
                    id="togglePasswordConfirmation"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-white/80 hover:text-white focus:outline-none transition-colors duration-200 z-10 hidden"
                >
                    <i class="fas fa-eye-slash" id="toggleIconConfirmation"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('login') }}" class="text-sm text-green-300 hover:underline">Already registered?</a>
            <x-primary-button class="bg-green-700 hover:bg-green-800 text-black dark:text-white">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>

{{-- JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const emailInput = document.getElementById('email');
        const emailWarning = document.getElementById('email-warning');

        emailInput.addEventListener('input', () => {
            const hasAtSymbol = emailInput.value.includes('@');
            emailWarning.classList.toggle('hidden', !hasAtSymbol);
            emailInput.setCustomValidity(hasAtSymbol ? "Please enter only your username, not the full email." : "");
        });

        const deptSelect = document.getElementById('department_id');
        const courseSelect = document.getElementById('course_id');
        const courseWrapper = document.getElementById('course-wrapper');

        deptSelect.addEventListener('change', function () {
            const deptId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const isGeDepartment = selectedOption.getAttribute('data-is-ge') === 'true';
            const geNotice = document.getElementById('ge-notice');
            
            if (!deptId) {
                courseWrapper.classList.add('hidden');
                courseSelect.innerHTML = '<option value="">-- Choose Course --</option>';
                geNotice.classList.add('hidden');
                return;
            }

            // Show/hide GE notice
            geNotice.classList.toggle('hidden', !isGeDepartment);

            if (isGeDepartment) {
                // For GE department, set a default course and hide course selection
                courseSelect.innerHTML = '<option value="1" selected>General Education</option>';
                courseWrapper.classList.add('hidden');
            } else {
                // For other departments, fetch courses normally
                courseSelect.innerHTML = '<option value="">Loading...</option>';
                fetch(`/api/department/${deptId}/courses`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 1) {
                            courseSelect.innerHTML = `<option value="${data[0].id}" selected>${data[0].name}</option>`;
                            courseWrapper.classList.add('hidden');
                        } else {
                            courseSelect.innerHTML = '<option value="">-- Choose Course --</option>';
                            data.forEach(course => {
                                courseSelect.innerHTML += `<option value="${course.id}">${course.name}</option>`;
                            });
                            courseWrapper.classList.remove('hidden');
                        }
                    });
            }
        });
        
        // Password toggle for main password field
        const toggleBtn = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('toggleIcon');
        const passwordInput = document.getElementById('password');
        const passwordVisible = document.getElementById('password-visible');
        
        if (toggleBtn && toggleIcon && passwordInput && passwordVisible) {
            let isVisible = false;
            
            const updateToggleVisibility = () => {
                if (passwordInput.value.length > 0 || passwordVisible.value.length > 0) {
                    toggleBtn.classList.remove('hidden');
                } else {
                    toggleBtn.classList.add('hidden');
                    if (isVisible) {
                        isVisible = false;
                        passwordVisible.style.display = 'none';
                        passwordInput.style.display = 'block';
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    }
                }
            };
            
            passwordInput.addEventListener('input', function() {
                passwordVisible.value = this.value;
                updateToggleVisibility();
            });
            
            passwordVisible.addEventListener('input', function() {
                passwordInput.value = this.value;
                updateToggleVisibility();
                checkPassword(this.value);
            });
            
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                isVisible = !isVisible;
                
                if (isVisible) {
                    passwordInput.style.display = 'none';
                    passwordVisible.style.display = 'block';
                    passwordVisible.style.position = 'relative';
                    passwordVisible.value = passwordInput.value;
                    passwordVisible.focus();
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                } else {
                    passwordVisible.style.display = 'none';
                    passwordInput.style.display = 'block';
                    passwordInput.value = passwordVisible.value;
                    passwordInput.focus();
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                }
            });
        }
        
        // Password toggle for confirmation field
        const toggleBtnConf = document.getElementById('togglePasswordConfirmation');
        const toggleIconConf = document.getElementById('toggleIconConfirmation');
        const passwordConfInput = document.getElementById('password_confirmation');
        const passwordConfVisible = document.getElementById('password_confirmation-visible');
        
        if (toggleBtnConf && toggleIconConf && passwordConfInput && passwordConfVisible) {
            let isVisibleConf = false;
            
            const updateToggleVisibilityConf = () => {
                if (passwordConfInput.value.length > 0 || passwordConfVisible.value.length > 0) {
                    toggleBtnConf.classList.remove('hidden');
                } else {
                    toggleBtnConf.classList.add('hidden');
                    if (isVisibleConf) {
                        isVisibleConf = false;
                        passwordConfVisible.style.display = 'none';
                        passwordConfInput.style.display = 'block';
                        toggleIconConf.classList.remove('fa-eye');
                        toggleIconConf.classList.add('fa-eye-slash');
                    }
                }
            };
            
            passwordConfInput.addEventListener('input', function() {
                passwordConfVisible.value = this.value;
                updateToggleVisibilityConf();
            });
            
            passwordConfVisible.addEventListener('input', function() {
                passwordConfInput.value = this.value;
                updateToggleVisibilityConf();
            });
            
            toggleBtnConf.addEventListener('click', function(e) {
                e.preventDefault();
                isVisibleConf = !isVisibleConf;
                
                if (isVisibleConf) {
                    passwordConfInput.style.display = 'none';
                    passwordConfVisible.style.display = 'block';
                    passwordConfVisible.style.position = 'relative';
                    passwordConfVisible.value = passwordConfInput.value;
                    passwordConfVisible.focus();
                    toggleIconConf.classList.remove('fa-eye-slash');
                    toggleIconConf.classList.add('fa-eye');
                } else {
                    passwordConfVisible.style.display = 'none';
                    passwordConfInput.style.display = 'block';
                    passwordConfInput.value = passwordConfVisible.value;
                    passwordConfInput.focus();
                    toggleIconConf.classList.remove('fa-eye');
                    toggleIconConf.classList.add('fa-eye-slash');
                }
            });
        }
    });

    function checkPassword(password) {
        const checks = {
            length: password.length >= 8,
            number: /[0-9]/.test(password),
            case: /[a-z]/.test(password) && /[A-Z]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        const update = (id, valid) => {
            const el = document.getElementById(`circle-${id}`);
            el.classList.remove('bg-red-400', 'bg-green-500', 'bg-gray-300');
            el.classList.add(valid ? 'bg-green-500' : 'bg-red-400');
        };

        update('length', checks.length);
        update('number', checks.number);
        update('case', checks.case);
        update('special', checks.special);

        const allValid = Object.values(checks).every(Boolean);
        document.getElementById('password-requirements').classList.toggle('hidden', allValid);
    }
</script>
@endsection
