<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Shop';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->live()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->required()
                        ->rules(['email', 'unique:users,email']),
                    Forms\Components\TextInput::make('phone')
                        ->required()
                        ->tel(),
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->required()
                        ->maxDate(now()),
                    Forms\Components\TextInput::make('city')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('zip_code')
                        ->required(),
                    Forms\Components\TextInput::make('address')
                        ->required()
                        ->maxLength(255)
                    ->columnSpanFull(),
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(isIndividual: true)
                ->sortable(),
                Tables\Columns\TextColumn::make('email')
                ->searchable(isIndividual: true)
                ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('city')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('zip_code')
                ->searchable(),
                Tables\Columns\TextColumn::make('address')
                ->searchable()
                ->sortable()
                ->limit(20)
                ->tooltip(fn ($record) => $record->address),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
