<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\Fulfillment;
use App\Enums\OrderStage;
use App\Enums\PaymentMethod;
use App\Enums\TimeSlot;
use App\Models\Flavor;
use App\Models\Size;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * The kitchen can adjust the stage, schedule and handwriting.
 * Money fields are read-only: prices are a record of what the customer agreed to.
 */
class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Jadwal')
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('pickup_date')
                            ->label('Tanggal')
                            ->native(false)
                            ->displayFormat('l, j F Y')
                            ->required(),
                        Select::make('time_slot')
                            ->label('Jam')
                            ->options(TimeSlot::class)
                            ->required(),
                        Select::make('stage')
                            ->label('Tahap')
                            ->options(OrderStage::class)
                            ->required(),
                        Select::make('fulfillment')
                            ->label('Ambil/antar')
                            ->options(Fulfillment::class)
                            ->live()
                            ->required(),
                        TextInput::make('delivery_address')
                            ->label('Alamat antar')
                            ->visible(fn (Get $get) => $get('fulfillment') === Fulfillment::Delivery
                                || $get('fulfillment') === Fulfillment::Delivery->value)
                            ->columnSpanFull(),
                    ]),
                Section::make('Pemesan')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('customer_whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode')
                            ->disabled(),
                    ]),
                Section::make('Kue')
                    ->columns(3)
                    ->columnSpan(2)
                    ->schema([
                        Select::make('cake_id')
                            ->label('Kue')
                            ->relationship('cake', 'name')
                            ->required(),
                        Select::make('size_id')
                            ->label('Ukuran')
                            ->relationship('size', 'diameter_cm')
                            ->getOptionLabelFromRecordUsing(fn (Size $record) => $record->label)
                            ->required(),
                        Select::make('flavor_id')
                            ->label('Rasa')
                            ->relationship('flavor', 'slug')
                            ->getOptionLabelFromRecordUsing(fn (Flavor $record) => $record->translate('name'))
                            ->required(),
                        TextInput::make('cake_text')
                            ->label('Tulisan di kue')
                            ->maxLength(config('alon.cake_text_max'))
                            ->columnSpanFull(),
                        Textarea::make('card_message')
                            ->label('Kartu pesan')
                            ->maxLength(config('alon.card_message_max'))
                            ->rows(3)
                            ->columnSpan(2),
                        TextInput::make('card_from')
                            ->label('Dari')
                            ->maxLength(config('alon.card_from_max')),
                    ]),
                Section::make('Pembayaran (simulasi)')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('cake_price')
                            ->label('Harga kue')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('delivery_fee')
                            ->label('Ongkos antar')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled(),
                        Select::make('payment_method')
                            ->label('Metode')
                            ->options(PaymentMethod::class)
                            ->disabled(),
                        DateTimePicker::make('paid_at')
                            ->label('Dibayar')
                            ->disabled(),
                    ]),
            ]);
    }
}
