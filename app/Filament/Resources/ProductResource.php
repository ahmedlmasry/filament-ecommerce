<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Nette\Utils\Image;
use PHPUnit\Metadata\Group;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->live(onBlur: true)
                                    ->maxLength(255)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('slug', Str::slug($state));
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->dehydrated()
                                    ->readOnly()
                                    ->unique(Product::class, 'slug', ignoreRecord: true)
                                    ->required(),
                                Forms\Components\RichEditor::make('description')
                                    ->nullable()
                                    ->columnSpan('full'),
                            ])->columns(2),
                        Forms\Components\Section::make('images')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->directory('products')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor()
                            ])->collapsible(),
                        Forms\Components\Section::make('pricing')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
                                Forms\Components\TextInput::make('sku')
                                    ->required()
                                    ->label('SKU (Stock Keeping Unit)'),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'downloadable' => 'downloadable',
                                        'deliverable' => 'deliverable',
                                    ])
                            ])
                            ->columns(2)
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('status')
                            ->schema([
                                Forms\Components\Toggle::make('visible')
                                    ->default(true)
                                    ->helperText('This product will be hidden from all sales channels.'),
                                Forms\Components\Toggle::make('featured')
                                    ->default(true),
                                Forms\Components\DatePicker::make('published_at')
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('associations')
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->relationship('brand', 'name'),
                                Forms\Components\Select::make('category')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->relationship('categories', 'name')
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sku')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('visibility')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('featured')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->sortable()->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->options([
                        'available' => true,
                        'not-available' => false,
                    ])
                    ->label('Visibility'),
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('categories', 'name'),
                Filter::make('is_featured')
                    ->query(fn(Builder $query) => $query->where('is_featured', true)),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
