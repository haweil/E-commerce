<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\ProductVariation;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ProductResource\Pages;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product Information')->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        TextInput::make('sku')
                            ->required()
                            ->maxLength(255),
                        Repeater::make('translations')
                            ->relationship()
                            ->schema([
                                TextInput::make('locale')
                                    ->required()
                                    ->label('Locale'),
                                TextInput::make('name')
                                    ->required()
                                    ->label('name'),
                            ])
                            ->columns(2)
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                // Perform any data transformation before saving if needed
                                return $data;
                            }),
                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products'),

                    ])->columns(2),
                    Section::make('Images')->schema([
                        FileUpload::make('images')
                            ->multiple()
                            ->directory('products')
                            ->maxFiles(7)
                            ->reorderable(),
                    ])
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make('Prices')->schema([
                        TextInput::make('base_price')
                            ->required()
                            ->numeric()
                            ->label('Base Price (USD)'),
                    ]),
                    Section::make('Sizes and Prices')->schema([
                        Toggle::make('has_sizes')
                            ->label('Has Sizes')
                            ->default(false)
                            ->reactive(),

                        Repeater::make('variations')
                            ->relationship()
                            ->schema([
                                TextInput::make('size')
                                    ->required()
                                    ->label('Size'),
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->label('Price (USD)'),
                            ])
                            ->columns(2)
                            ->hidden(fn($get) => !$get('has_sizes'))
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                // Perform any data transformation before saving if needed
                                return $data;
                            }),
                    ]),

                    Section::make('Associations')->schema([
                        Select::make('category_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name'),

                        Select::make('brand_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name')
                    ]),
                    Section::make('Status')->schema([
                        Toggle::make('in_stock')
                            ->required()
                            ->default(true),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Toggle::make('is_featured')
                            ->required(),
                        Toggle::make('on_sale')
                            ->required(),
                    ])
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->boolean(),
                IconColumn::make('on_sale')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('in_stock')
                    ->boolean(),
                TextColumn::make('prices')
                    ->label('Prices')
                    ->state(function (Product $record): string {
                        $html = '<div class="space-y-1">';

                        // Base Price Section
                        if ($record->base_price > 0) {
                            $html .= sprintf(
                                '<div class="flex items-center gap-2">
                                    <div class="px-2 py-1 bg-primary-50 rounded-md">
                                        <span class="text-sm font-medium">Base Price:</span>
                                        <span class="text-primary-600">$%s</span>
                                    </div>
                                    %s
                                </div>',
                                number_format($record->base_price, 2),
                                $record->on_sale ? '<span class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded">On Sale</span>' : ''
                            );
                        }


                        // Show variations if they exist
                        if ($record->has_sizes && $record->variations->count() > 0) {
                            foreach ($record->variations as $variation) {
                                $html .= sprintf(
                                    '<div class="text-sm text-gray-600">%s - $%s</div>',
                                    $variation->size,
                                    number_format($variation->price, 2)
                                );
                            }
                        }

                        $html .= '</div>';
                        return $html;
                    })
                    ->html()
                    ->alignLeft(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->indicator('Category'),
                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->indicator('Brand'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['variations']);
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
