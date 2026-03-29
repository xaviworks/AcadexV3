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
                    @if($geDepartmentId && (int) $dept->id === (int) $geDepartmentId && (string) old('department_id') !== (string) $dept->id)
                        @continue
                    @endif
                    <option
                        value="{{ $dept->id }}"
                        data-is-ase="{{ ($aseDepartment && (int) $dept->id === (int) $aseDepartment->id) ? 'true' : 'false' }}"
                        {{ (string) old('department_id') === (string) $dept->id ? 'selected' : '' }}
                    >
                        {{ $dept->department_description }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('department_id')" class="mt-2 text-red-400" />
        </div>

        {{-- Program --}}
        <div class="hidden transition-opacity duration-300" id="course-wrapper">
            <x-input-label for="course_id" :value="__('Select Program')" class="text-white" />
            <select id="course_id" name="course_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm bg-gray-400 text-white" required>
                <option value="">-- Choose Program --</option>
            </select>
            <x-input-error :messages="$errors->get('course_id')" class="mt-2 text-red-400" />
        </div>

        {{-- GE Notice --}}
        <div id="ge-notice" class="hidden p-4 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 text-white mt-2">
            <strong class="font-semibold">General Education Instructor:</strong>
            <span class="block sm:inline">You selected the General Education program under ASE. This registration will be routed to the GE Coordinator.</span>
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-white" />
            <div class="relative" data-password-toggle-group>
                <input
                    id="password"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1 pr-12 text-white placeholder-white bg-transparent"
                    type="password"
                    name="password"
                    required
                    placeholder="Min. 8 characters"
                    autocomplete="new-password"
                    oninput="checkPassword(this.value)"
                    data-password-toggle-input
                />

                <button
                    type="button"
                    id="togglePassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-white/80 hover:text-white focus:outline-none transition-colors duration-200 z-10 hidden"
                    data-password-toggle-button
                    aria-label="Show password"
                    title="Show password"
                >
                    <i class="fas fa-eye-slash" id="toggleIcon" data-password-toggle-icon></i>
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
            <div class="relative" data-password-toggle-group>
                <input
                    id="password_confirmation"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full mt-1 pr-12 text-white placeholder-white bg-transparent"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    data-password-toggle-input
                />

                <button
                    type="button"
                    id="togglePasswordConfirmation"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-white/80 hover:text-white focus:outline-none transition-colors duration-200 z-10 hidden"
                    data-password-toggle-button
                    aria-label="Show password"
                    title="Show password"
                >
                    <i class="fas fa-eye-slash" id="toggleIconConfirmation" data-password-toggle-icon></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('login') }}" class="text-sm text-green-300 hover:underline">Already registered?</a>
            <button type="submit" style="background-color: #198754; border-color: #198754;" class="inline-flex items-center px-4 py-2 text-white rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</div>

{{-- JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const emailInput = document.getElementById('email');
        const emailWarning = document.getElementById('email-warning');
        const oldCourseId = @json(old('course_id'));

        emailInput.addEventListener('input', () => {
            const hasAtSymbol = emailInput.value.includes('@');
            emailWarning.classList.toggle('hidden', !hasAtSymbol);
            emailInput.setCustomValidity(hasAtSymbol ? "Please enter only your username, not the full email." : "");
        });

        const deptSelect = document.getElementById('department_id');
        const courseSelect = document.getElementById('course_id');
        const courseWrapper = document.getElementById('course-wrapper');
        const geNotice = document.getElementById('ge-notice');

        function updateGeNoticeVisibility() {
            const selectedCourse = courseSelect.options[courseSelect.selectedIndex];
            const isGeProgram = selectedCourse?.getAttribute('data-is-ge-program') === 'true';
            geNotice.classList.toggle('hidden', !isGeProgram);
        }

        deptSelect.addEventListener('change', function () {
            const deptId = this.value;
            
            if (!deptId) {
                courseWrapper.classList.add('hidden');
                courseSelect.innerHTML = '<option value="">-- Choose Program --</option>';
                geNotice.classList.add('hidden');
                return;
            }

            courseSelect.innerHTML = '<option value="">Loading...</option>';

            fetch(`/api/department/${deptId}/courses`)
                .then(response => response.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        courseSelect.innerHTML = '<option value="">No programs available</option>';
                        courseWrapper.classList.remove('hidden');
                        geNotice.classList.add('hidden');
                        return;
                    }

                    if (data.length === 1) {
                        const onlyCourse = data[0];
                        const selected = String(oldCourseId || '') === String(onlyCourse.id) ? 'selected' : 'selected';
                        const geFlag = onlyCourse.is_ge_program ? 'true' : 'false';
                        const label = onlyCourse.code ? `${onlyCourse.code} - ${onlyCourse.name}` : onlyCourse.name;

                        courseSelect.innerHTML = `<option value="${onlyCourse.id}" data-is-ge-program="${geFlag}" ${selected}>${label}</option>`;
                        courseWrapper.classList.add('hidden');
                        updateGeNoticeVisibility();
                        return;
                    }

                    courseSelect.innerHTML = '<option value="">-- Choose Program --</option>';
                    data.forEach(course => {
                        const isSelected = String(oldCourseId || '') === String(course.id) ? ' selected' : '';
                        const geFlag = course.is_ge_program ? 'true' : 'false';
                        const label = course.code ? `${course.code} - ${course.name}` : course.name;

                        courseSelect.innerHTML += `<option value="${course.id}" data-is-ge-program="${geFlag}"${isSelected}>${label}</option>`;
                    });

                    courseWrapper.classList.remove('hidden');
                    updateGeNoticeVisibility();
                })
                .catch(() => {
                    courseSelect.innerHTML = '<option value="">Error loading programs</option>';
                    courseWrapper.classList.remove('hidden');
                    geNotice.classList.add('hidden');
                });
        });

        courseSelect.addEventListener('change', updateGeNoticeVisibility);

        if (deptSelect.value) {
            deptSelect.dispatchEvent(new Event('change'));
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
