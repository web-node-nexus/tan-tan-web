<?php

namespace App\Filament\Resources\KycSubmissions;

use App\Filament\Resources\KycSubmissions\Pages\ListKycSubmissions;
use App\Filament\Resources\KycSubmissions\Pages\ViewKycSubmission;
use App\Models\KycSubmission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class KycSubmissionResource extends Resource
{
    protected static ?string $model = KycSubmission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Trust & safety';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'KYC';

    protected static ?string $modelLabel = 'KYC submission';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identity')->columns(2)->schema([
                TextEntry::make('user.name')->label('Account'),
                TextEntry::make('legal_name'),
                TextEntry::make('document_type'),
                TextEntry::make('document_last4')->label('Last 4'),
                TextEntry::make('status'),
                TextEntry::make('note')->placeholder('—'),
                ImageEntry::make('document_path')->label('Document')->disk('public')->columnSpanFull(),
                ImageEntry::make('selfie_path')->label('Selfie')->disk('public')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Account')->searchable(),
                TextColumn::make('legal_name')->searchable(),
                TextColumn::make('document_type'),
                TextColumn::make('document_last4')->label('Last 4'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
                self::approveAction(),
                self::rejectAction(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->color('success')
            ->visible(fn (KycSubmission $record): bool => $record->status === 'pending')
            ->action(function (KycSubmission $record): void {
                $record->forceFill(['status' => 'approved', 'note' => null, 'reviewed_at' => now()])->save();
                $record->user?->forceFill(['is_verified' => true, 'kyc_status' => 'approved'])->save();
                Notification::make()->success()->title('KYC approved')->send();
            });
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->color('danger')
            ->visible(fn (KycSubmission $record): bool => $record->status === 'pending')
            ->schema([
                Textarea::make('note')->label('Reason')->required()->maxLength(200),
            ])
            ->action(function (KycSubmission $record, array $data): void {
                $record->forceFill([
                    'status' => 'rejected',
                    'note' => $data['note'],
                    'reviewed_at' => now(),
                ])->save();
                $record->user?->forceFill(['is_verified' => false, 'kyc_status' => 'rejected'])->save();
                Notification::make()->danger()->title('KYC rejected')->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKycSubmissions::route('/'),
            'view' => ViewKycSubmission::route('/{record}'),
        ];
    }
}
