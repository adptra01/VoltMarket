<?php

use Dipantry\Rajaongkir\Models\ROProvince;
use Dipantry\Rajaongkir\Models\ROCity;
use App\Models\Address;
use function Livewire\Volt\{state, computed, rules};

state(['province_id'])->url();

rules([
    'province_id' => 'required|exists:rajaongkir_provinces,id',
    'city_id' => 'required|exists:rajaongkir_cities,id',
    'details' => 'required|min:20',
]);

$getAddress = computed(function () {
    return Address::where('user_id', auth()->id())->first();
});

state([
    'province_id' => fn() => $this->getAddress->province_id ?? '',
    'city_id' => fn() => $this->getAddress->city_id ?? '',
    'details' => fn() => $this->getAddress->details ?? '',
    'provinces' => fn() => ROProvince::all(),
]);

$cities = computed(function () {
    return ROCity::where('province_id', $this->province_id)->get();
});

$submit = function () {
    $validate = $this->validate();

    if ($this->getAddress) {
        $updateAddress = Address::where('user_id', auth()->id())->first();
        $updateAddress->update($validate);
    } elseif (!$this->getAddress) {
        $validate['user_id'] = auth()->id();
        Address::create($validate);
    }
    $this->dispatch('address-update');
};

?>
@volt
    <div>
        <div class="alert alert-dark alert-dismissible fade show" role="alert">


            <strong>Kamu dapat melihat dan memperbarui detail alamat kamu, seperti nama provinsi, kota dan detail lengkap
                yang sesuai tujuanmu.</strong>
        </div>
        <form wire:submit.prevent="submit">
            @csrf
            <div class="mb-3">
                <label for="province_id" class="form-label">Pilih Provinsi</label>
                <select wire:model.live="province_id" wire:loading.attr="disabled" class="form-select" name="province_id"
                    id="province_id">
                    <option>Pick one</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}">
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
                @error('province_id')
                    <p class="text-danger">
                        {{ $message }}
                    </p>
                @enderror
            </div>
            <div class="mb-3">
                <label for="city_id" class="form-label">Pilih Kota</label>
                <select wire:model="city_id" wire:loading.attr="disabled" class="form-select" name="city_id" id="city_id">
                    <option selected>Pick one</option>
                    @foreach ($this->cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
                @error('province_id')
                    <p class="text-danger">
                        {{ $message }}
                    </p>
                @enderror
            </div>
            <div class="mb-3">
                <label for="details" class="form-label">
                    Detail Lengkap
                </label>
                <textarea class="form-control" wire:model='details' name="details" id="details" rows="3"></textarea>
                @error('details')
                    <p class="text-danger">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            <div class="mb-3 d-flex justify-content-end align-items-center">

                {{-- Loading Spinner --}}
                <div wire:loading class="spinner-border spinner-border-sm mx-5" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>

                {{-- Success Notif --}}
                <x-action-message class="me-3" on="address-update">
                    Berhasil
                </x-action-message>

                <button type="submit" class="btn btn-dark">
                    Submit
                </button>

            </div>
        </form>
    </div>
@endvolt
