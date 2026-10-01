{{-- Shared by create/edit. $item is null on create. --}}
@php $item = $item ?? null; @endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $item?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label for="email">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $item?->email) }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $item?->phone) }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-3">
        <label for="phone_verified">Phone Verified <span class="text-danger">*</span></label>
        <select id="phone_verified" name="phone_verified" class="form-control @error('phone_verified') is-invalid @enderror">
            @php $pv = (string) old('phone_verified', $item ? (int) $item->phone_verified : 1); @endphp
            <option value="1" @selected($pv === '1')>Yes</option>
            <option value="0" @selected($pv === '0')>No</option>
        </select>
        @error('phone_verified')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-3">
        <label for="email_verified">Email Verified</label>
        <select id="email_verified" name="email_verified" class="form-control">
            @php $ev = (string) old('email_verified', $item?->email_verified_at ? 1 : 0); @endphp
            <option value="1" @selected($ev === '1')>Yes</option>
            <option value="0" @selected($ev === '0')>No</option>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="user_type">User Type <span class="text-danger">*</span></label>
        <select id="user_type" name="user_type" class="form-control @error('user_type') is-invalid @enderror" required>
            @foreach($userTypes as $t)
                <option value="{{ $t }}" @selected(old('user_type', $item?->user_type ?? 'applicant') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        @error('user_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label for="department_id">Department <small class="text-muted">(required for Head)</small></label>
        <select id="department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected((string) old('department_id', $item?->department_id) === (string) $d->id)>
                    {{ $d->short_name }} — {{ $d->full_name }}
                </option>
            @endforeach
        </select>
        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="password">
            Password @if(!$item)<span class="text-danger">*</span>@else<small class="text-muted">(leave blank to keep current)</small>@endif
        </label>
        <input type="text" id="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" {{ $item ? '' : 'required' }}>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label for="password_confirmation">Confirm Password</label>
        <input type="text" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
               class="form-control" {{ $item ? '' : 'required' }}>
    </div>
</div>
