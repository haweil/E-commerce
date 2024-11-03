<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Tables\Columns\Text;
use Filament\Tables\Table;
use App\Models\Configuration;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ConfigurationResource\Pages;

class ConfigurationResource extends Resource
{
    protected static ?string $model = Configuration::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Google OAuth settings
            Card::make()->schema([
                TextInput::make('google_client_id')
                    ->label('Google Client ID')
                    ->placeholder('Enter Google Client ID'),

                TextInput::make('google_client_secret')
                    ->label('Google Client Secret')
                    ->placeholder('Enter Google Client Secret'),

                TextInput::make('google_redirect_uri')
                    ->label('Google Redirect URI')
                    ->placeholder('Enter Google Redirect URI'),
            ])->label('Google OAuth Settings'),

            // PayPal settings
            Card::make()->schema([
                TextInput::make('paypal_client_id')
                    ->label('PayPal Client ID')
                    ->placeholder('Enter PayPal Client ID'),

                TextInput::make('paypal_client_secret')
                    ->label('PayPal Client Secret')
                    ->placeholder('Enter PayPal Client Secret'),

                Select::make('paypal_mode')
                    ->label('PayPal Mode')
                    ->options([
                        'sandbox' => 'Sandbox',
                        'live' => 'Live',
                    ])
            ])->label('PayPal Settings'),

            // Stripe settings
            Card::make()->schema([
                TextInput::make('stripe_public_key')
                    ->label('Stripe Public Key')
                    ->placeholder('Enter Stripe Public Key'),

                TextInput::make('stripe_secret_key')
                    ->label('Stripe Secret Key')
                    ->placeholder('Enter Stripe Secret Key'),
            ])->label('Stripe Settings'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('google_client_id')
                    ->label('Google Client ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('google_client_secret')
                    ->label('Google Client Secret')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('google_redirect_uri')
                    ->label('Google Redirect URI')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('paypal_client_id')
                    ->label('PayPal Client ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('paypal_client_secret')
                    ->label('PayPal Client Secret')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('paypal_mode')
                    ->label('PayPal Mode')
                    ->sortable(),

                TextColumn::make('stripe_public_key')
                    ->label('Stripe Public Key')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('stripe_secret_key')
                    ->label('Stripe Secret Key')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('paypal_mode')
                    ->label('PayPal Mode')
                    ->options([
                        'sandbox' => 'Sandbox',
                        'live' => 'Live',
                    ]),
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
            // Define relations if any
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfigurations::route('/'),
            'create' => Pages\CreateConfiguration::route('/create'),
            'edit' => Pages\EditConfiguration::route('/{record}/edit'),
        ];
    }
}
