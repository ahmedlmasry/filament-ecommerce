<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 4;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status','processing')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', '=', 'processing')->count() > 10
            ? 'warning'
            : 'primary';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Order Details')
                        ->schema([
                            Forms\Components\TextInput::make('number')
                                ->default('OR-' . random_int(10000, 99999))
                                ->disabled()
                                ->dehydrated(),
                            Forms\Components\Group::make([
                                Forms\Components\Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->preload()
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('shipping_price')
                                    ->label('Shipping Costs')
                                    ->dehydrated()
                                    ->numeric()
                                    ->required(),
                                Forms\Components\ToggleButtons::make('status')
                                    ->options([
                                        'pending' => 'pending',
                                        'processing' => 'processing',
                                        'completed' => 'completed',
                                        'declined' => 'declined'
                                    ])
                                    ->icons([
                                        'pending' => 'heroicon-o-exclamation-circle',
                                        'processing' => 'heroicon-o-arrow-path',
                                        'completed' => 'heroicon-o-check-circle',
                                        'declined' => 'heroicon-o-x-circle',
                                    ])->columns(3)->gridDirection('row')->grouped(),
                            ])->columns(2),
                            Forms\Components\RichEditor::make('notes')
                                ->columnSpanFull()

                        ]),
                    Forms\Components\Wizard\Step::make('Order Items')
                        ->schema([
                            Forms\Components\Repeater::make('Items')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Product')
                                        ->options(Product::pluck('name', 'id')->toArray())
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, $get) {
                                            $product = Product::find($state);
                                            $qty = $get('quantity') ?? 1;
                                            if ($product) {
                                                $set('price', $product->price);
                                                $set('total_price', $product->price * $qty);
                                            } else {
                                                $set('price', null);
                                            }
                                        }),
                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, $get) {
                                            $price = $get('price') ?? 0;
                                            $set('total_price', $price * $state);
                                        }),
                                    Forms\Components\TextInput::make('price')
                                        ->disabled()
                                        ->numeric()
                                        ->dehydrated(),
                                    TextInput::make('total_price')
                                        ->label('Total Price')
                                        ->numeric()
                                        ->readOnly()
                                        ->reactive(),
                                ])
                                ->columns(4)->afterStateUpdated(function ($state, callable $set) {
                                    $total = collect($state)->sum(fn($item) => $item['total_price']);
                                    $set('total', $total);
                                }),
                            TextInput::make('total')
                                ->label('Total')
                                ->disabled()
                                ->dehydrated()
                        ])->columns(1),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'declined' => 'danger',
                    })->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-exclamation-circle',
                        'processing' => 'heroicon-o-arrow-path',
                        'completed' => 'heroicon-o-check-circle',
                        'declined' => 'heroicon-o-x-circle',
                    })
                ,
                Tables\Columns\TextColumn::make('total')
                    ->searchable()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                Tables\Columns\TextColumn::make('shipping_price')
                    ->label('Shipping cost')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
