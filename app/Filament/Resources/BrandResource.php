<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use PHPUnit\Metadata\Group;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Shop';
    protected static  string $routeName = 'brand';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('slug', Str::slug($state));
                        }),
                    TextInput::make('slug')
                        ->disabled()
                        ->unique(Brand::class, 'slug', ignoreRecord: true)
                        ->dehydrated(),
                    TextInput::make('url')
                        ->required()
                        ->maxLength(255)
                        ->label('website')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_visible')
                        ->default(true)
                        ->label('visible to customers'),
                    Forms\Components\Group::make([
                        Forms\Components\ColorPicker::make('primary_hex')
                            ->label('primary color')
                    ])->columns(3),
                    Forms\Components\RichEditor::make('description')
                ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('Website')
                    ->sortable(),
                Tables\Columns\ColorColumn::make('primary_hex')
                    ->label('primary color'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->label('visibility')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
