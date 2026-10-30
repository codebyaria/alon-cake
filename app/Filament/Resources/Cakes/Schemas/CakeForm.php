<?php

namespace App\Filament\Resources\Cakes\Schemas;

use App\Models\Cake;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CakeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Kue')
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->helperText('Juga tulisan khas di kue, mis. "Good things take time".')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set, ?Cake $record) => $record ? null : $set('slug', Str::slug($state)))
                            ->required(),
                        TextInput::make('slug')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        TextInput::make('decor_note.id')
                            ->label('Hiasan singkat (Indonesia)')
                            ->placeholder('Piping gelombang, tulisan tangan')
                            ->required(),
                        TextInput::make('decor_note.en')
                            ->label('Hiasan singkat (English)')
                            ->placeholder('Wavy piping, handwriting')
                            ->required(),
                        Textarea::make('description.id')
                            ->label('Deskripsi (Indonesia)')
                            ->rows(3)
                            ->required(),
                        Textarea::make('description.en')
                            ->label('Deskripsi (English)')
                            ->rows(3)
                            ->required(),
                    ]),
                Section::make('Harga & tampilan')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('base_price')
                            ->label('Harga ukuran terkecil')
                            ->helperText('Ukuran lebih besar menambah harga sesuai menu Ukuran.')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        FileUpload::make('photo_path')
                            ->label('Foto diam')
                            ->image()
                            ->disk('public')
                            ->directory('cakes'),
                        TextInput::make('sort')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Ditampilkan di menu')
                            ->default(true),
                    ]),
            ]);
    }
}
