<?php

use function Livewire\Volt\{state, rules};
use Dipantry\Rajaongkir\Constants\RajaongkirCourier;
use App\Models\Order;
use App\Models\Item;
use function Laravel\Folio\name;

name('transactions.show');

state([
    'order' => fn() => Order::find($id),
    'orderItems' => fn() => Item::where('order_id', $this->order->id)->get(),
    'tracking_number',
]);

rules([
    'tracking_number' => 'required|min:10',
]);

$confirm = function () {
    $this->order->update(['status' => 'PACKED']);
    $this->dispatch('order-update');
};

$saveTrackingNumber = function () {
    $validate = $this->validate();
    $validate['status'] = 'SHIPPED';

    $this->order->update($validate);
    $this->dispatch('order-update');
};

?>
<x-admin-layout>
    <x-slot name="title">Transaksi {{ $order->invoice }}</x-slot>
    <x-slot name="header">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transaksi</a></li>
        <li class="breadcrumb-item"><a
                href="{{ route('transactions.show', ['order' => $order->id]) }}">{{ $order->invoice }}</a></li>
    </x-slot>
    @volt
        <div>
            <div class="card d-print-none">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md">
                            <form wire:submit="saveTrackingNumber">
                                @if ($order->status == 'PACKED')
                                    <div class="input-group mb-3">
                                        <input wire:model="tracking_number" type="text"
                                            class="form-control  @error('tracking_number')
                                        is-invalid
                                        @enderror"
                                            placeholder="Masukkan resi...">

                                        <button class="btn btn-primary" type="submit">
                                            <i class="ti ti-check fs-5"></i>
                                        </button>
                                        <x-action-message wire:loading on="saveTrackingNumber">
                                            <span class="spinner-border spinner-border-sm"></span>
                                        </x-action-message>
                                    </div>
                                    @error('tracking_number')
                                        <small id="tracking_numberId" class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                @endif
                            </form>
                        </div>
                        <div class="col-md">
                            <div class="text-end">
                                @if ($order->status == 'PENDING')
                                    <x-action-message wire:loading on="password-updated">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </x-action-message>
                                    <button wire:click='confirm' class="btn btn-primary" type="submit">
                                        <i class="ti ti-circle-check fs-3"></i>
                                        Proses Pesanan
                                    </button>
                                @endif
                                <button class="btn btn-primary print-page ms-6" onclick="window.print()" type="button">
                                    <span>
                                        <i class="ti ti-printer fs-3"></i>
                                        Print
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card d-print-block">
                <div class="card-body">
                    <div class="invoice-123" id="printableArea" style="display: block;">
                        <div class="row pt-3">
                            <div class="col-md-12">
                                <div>
                                    <address>
                                        <h6>Pesanan Dari,</h6>
                                        <p>
                                            {{ $order->user->name }} - {{ $order->status }} <br>
                                            {{ $order->user->email }} <br>
                                            {{ $order->user->telp }}
                                        </p>
                                        <h6>
                                            {{ $order->user->address->province->name }},
                                            {{ $order->user->address->city->name }} <br>
                                        </h6>
                                        <p>
                                            {{ $order->user->address->details }}
                                        </p>
                                    </address>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md">
                                        <h6>Nomor Faktur:
                                            {{ $order->invoice }}
                                        </h6>
                                        <h6>Nomor Resi Pesanan:
                                            {{ $order->tracking_number ?? '-' }}
                                        </h6>
                                        <h6>Pengiriman:
                                            {{ $order->courier }}
                                        </h6>
                                        <h6>Tambahan:
                                            {{ $order->protect_cost == true ? 'Bubble Wrap' : '-' }}
                                        </h6>

                                        <h6>Metode Pembayaran:
                                            {{ $order->payment_method }}
                                        </h6>
                                    </div>
                                    @if ($order->payment_method == 'Transfer Bank')
                                        <div class="col-md text-end">
                                            <figure class="figure">
                                                <img src="{{ Storage::url($order->proof_of_payment) }}"
                                                    class="figure-img img-fluid rounded object-fit-cover
                                            {{ !$order->proof_of_payment ? 'placeholder' : '' }}"
                                                    width="100" alt="...">
                                                <figcaption class="figure-caption text-center">
                                                    Bukti Pembayaran
                                                </figcaption>
                                            </figure>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="table-responsive mt-3">
                                    <table class="table table-borderless table-sm">
                                        <thead>
                                            <!-- start row -->
                                            <tr class="border">
                                                <th class="text-center">#</th>
                                                <th>Produk</th>
                                                <th class="text-end">Kuantitas</th>
                                                <th class="text-end">Harga Satuan</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                            <!-- end row -->
                                        </thead>
                                        <tbody>
                                            @foreach ($orderItems as $no => $item)
                                                <!-- start row -->
                                                <tr class="border">
                                                    <td class="text-center">{{ ++$no }}</td>
                                                    <td>{{ Str::limit($item->product->title, 30, '...') }}</td>
                                                    <td class="text-end">{{ $item->qty }}</td>
                                                    <td class="text-end">
                                                        {{ 'Rp.' . Number::format($item->product->price, locale: 'id') }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ 'Rp.' . Number::format($item->product->price * $item->qty, locale: 'id') }}
                                                    </td>
                                                </tr>
                                                <!-- end row -->
                                            @endforeach

                                            <tr class="text-end">
                                                <td colspan="4"> Sub - Total:</td>
                                                <td>
                                                    {{ 'Rp.' .
                                                        Number::format(
                                                            $order->items->sum(function ($item) {
                                                                return $item->qty * $item->product->price;
                                                            }),
                                                            locale: 'id',
                                                        ) }}
                                                </td>
                                            </tr>
                                            <tr class="text-end">
                                                <td colspan="4"> Berat Barang:</td>
                                                <td>
                                                    {{ $order->total_weight }} gram
                                                </td>
                                            </tr>
                                            <tr class="text-end">
                                                <td colspan="4"> Biaya Pengiriman:</td>
                                                <td>
                                                    {{ 'Rp.' . Number::format($order->shipping_cost, locale: 'id') }}
                                                </td>
                                            </tr>
                                            <tr class="text-end">
                                                <td colspan="4"> Biaya Tambahan:</td>
                                                <td>
                                                    {{ $order->protect_cost == true ? 'Rp.' . Number::format(3000, locale: 'id') : 'Rp. 0' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="pull-right text-end">
                                    <h3>
                                        <b>Total : {{ 'Rp.' . Number::format($order->total_amount, locale: 'id') }} </b>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endvolt
    </x-app-layout>
