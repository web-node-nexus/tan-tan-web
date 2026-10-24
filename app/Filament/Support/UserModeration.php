<?php

namespace App\Filament\Support;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserModeration
{
    public const SUSPENDED_NOTE = 'Seller account suspended';

    public static function ban(): Action
    {
        return Action::make('ban')
            ->label('Suspend')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->visible(fn (User $record): bool => ! $record->isBanned() && $record->id !== auth()->id())
            ->modalHeading(fn (User $record): string => 'Suspend '.$record->name)
            ->modalDescription('Signs the user out of every device, blocks future logins and hides their live ads.')
            ->modalSubmitActionLabel('Suspend user')
            ->schema([
                Textarea::make('reason')->label('Reason')->required()->rows(3)->maxLength(500),
                Toggle::make('hide_listings')->label('Block their active listings')->default(true),
            ])
            ->action(function (User $record, array $data): void {
                self::applyBan($record, $data['reason'], (bool) $data['hide_listings']);
                Notification::make()->danger()->title($record->name.' suspended')->send();
            });
    }

    public static function unban(): Action
    {
        return Action::make('unban')
            ->label('Reinstate')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('success')
            ->visible(fn (User $record): bool => $record->isBanned())
            ->schema([
                Toggle::make('restore_listings')->label('Re-activate listings blocked by the suspension')->default(true),
            ])
            ->action(function (User $record, array $data): void {
                self::applyUnban($record, (bool) $data['restore_listings']);
                Notification::make()->success()->title($record->name.' reinstated')->send();
            });
    }

    public static function toggleVerified(): Action
    {
        return Action::make('toggleVerified')
            ->label(fn (User $record): string => $record->is_verified ? 'Remove badge' : 'Verify')
            ->icon(fn (User $record): Heroicon => $record->is_verified ? Heroicon::OutlinedShieldExclamation : Heroicon::OutlinedCheckBadge)
            ->color(fn (User $record): string => $record->is_verified ? 'gray' : 'success')
            ->requiresConfirmation()
            ->action(function (User $record): void {
                $record->forceFill(['is_verified' => ! $record->is_verified])->save();
                Notification::make()->success()
                    ->title($record->is_verified ? 'Verified badge granted' : 'Verified badge removed')
                    ->send();
            });
    }

    public static function bulkVerify(): BulkAction
    {
        return BulkAction::make('bulkVerify')
            ->label('Verify selected')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('success')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                User::query()->whereKey($records->modelKeys())->update(['is_verified' => true]);
                Notification::make()->success()->title($records->count().' users verified')->send();
            });
    }

    public static function bulkBan(): BulkAction
    {
        return BulkAction::make('bulkBan')
            ->label('Suspend selected')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->schema([
                Textarea::make('reason')->required()->rows(3)->maxLength(500),
            ])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $targets = $records->reject(fn (User $u): bool => $u->id === auth()->id() || $u->isBanned());
                $targets->each(fn (User $u) => self::applyBan($u, $data['reason'], true));
                Notification::make()->danger()->title($targets->count().' users suspended')->send();
            });
    }

    public static function applyBan(User $user, string $reason, bool $hideListings): void
    {
        DB::transaction(function () use ($user, $reason, $hideListings): void {
            $user->forceFill(['banned_at' => now(), 'ban_reason' => $reason])->save();
            $user->tokens()->delete();

            if ($hideListings) {
                $user->listings()->whereIn('status', ['active', 'pending'])->update([
                    'status' => 'blocked',
                    'is_featured' => false,
                    'moderation_note' => self::SUSPENDED_NOTE,
                    'moderated_at' => now(),
                ]);
            }
        });
    }

    public static function applyUnban(User $user, bool $restoreListings): void
    {
        DB::transaction(function () use ($user, $restoreListings): void {
            $user->forceFill(['banned_at' => null, 'ban_reason' => null])->save();

            if ($restoreListings) {
                $user->listings()
                    ->where('status', 'blocked')
                    ->where('moderation_note', self::SUSPENDED_NOTE)
                    ->update(['status' => 'active', 'moderation_note' => null, 'moderated_at' => now()]);
            }
        });
    }
}
