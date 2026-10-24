<?php

namespace App\Filament\Support;

use App\Models\Listing;
use App\Models\ListingPromotion;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListingModeration
{
    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'pending' => 'Pending review',
        'active' => 'Active',
        'sold' => 'Sold',
        'expired' => 'Expired',
        'rejected' => 'Rejected',
        'blocked' => 'Blocked',
    ];

    public const CONDITION_LABELS = [
        'new' => 'New',
        'like_new' => 'Like new',
        'used' => 'Used',
        'for_parts' => 'For parts',
    ];

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'pending' => 'warning',
            'sold' => 'info',
            'rejected', 'blocked' => 'danger',
            default => 'gray',
        };
    }

    public static function statusIcon(?string $status): Heroicon
    {
        return match ($status) {
            'active' => Heroicon::CheckCircle,
            'pending' => Heroicon::Clock,
            'sold' => Heroicon::ShoppingBag,
            'rejected' => Heroicon::XCircle,
            'blocked' => Heroicon::NoSymbol,
            'expired' => Heroicon::ArchiveBox,
            default => Heroicon::PencilSquare,
        };
    }

    public static function approve(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Listing $record): bool => in_array($record->status, ['pending', 'draft', 'rejected', 'blocked'], true))
            ->requiresConfirmation()
            ->modalDescription('The ad goes live in the app immediately.')
            ->action(function (Listing $record): void {
                self::applyStatus($record, 'active');
                Notification::make()->success()->title('Listing approved')->send();
            });
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Listing $record): bool => in_array($record->status, ['pending', 'active', 'draft'], true))
            ->modalHeading('Reject listing')
            ->modalSubmitActionLabel('Reject')
            ->schema([self::noteField('Reason shown to the seller')])
            ->action(function (Listing $record, array $data): void {
                self::applyStatus($record, 'rejected', $data['note']);
                Notification::make()->danger()->title('Listing rejected')->send();
            });
    }

    public static function block(): Action
    {
        return Action::make('block')
            ->label('Block')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->visible(fn (Listing $record): bool => $record->status !== 'blocked')
            ->modalHeading('Block listing')
            ->modalDescription('Blocked ads are hidden everywhere and cannot be re-published by the seller.')
            ->modalSubmitActionLabel('Block')
            ->schema([self::noteField('Internal reason (policy violation, scam, duplicate…)')])
            ->action(function (Listing $record, array $data): void {
                self::applyStatus($record, 'blocked', $data['note']);
                Notification::make()->danger()->title('Listing blocked')->send();
            });
    }

    public static function feature(): Action
    {
        return Action::make('feature')
            ->label('Feature')
            ->icon(Heroicon::OutlinedSparkles)
            ->color('warning')
            ->visible(fn (Listing $record): bool => $record->status === 'active' && ! self::isFeaturedNow($record))
            ->modalHeading('Feature this listing')
            ->modalSubmitActionLabel('Feature')
            ->schema([self::daysField()])
            ->action(function (Listing $record, array $data): void {
                self::applyFeature($record, (int) $data['days']);
                Notification::make()->success()->title('Listing featured for '.$data['days'].' days')->send();
            });
    }

    public static function unfeature(): Action
    {
        return Action::make('unfeature')
            ->label('Remove feature')
            ->icon(Heroicon::OutlinedStar)
            ->color('gray')
            ->visible(fn (Listing $record): bool => self::isFeaturedNow($record))
            ->requiresConfirmation()
            ->action(function (Listing $record): void {
                self::removeFeature($record);
                Notification::make()->success()->title('Feature removed')->send();
            });
    }

    public static function bulkApprove(): BulkAction
    {
        return BulkAction::make('bulkApprove')
            ->label('Approve selected')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $records->each(fn (Listing $l) => self::applyStatus($l, 'active'));
                Notification::make()->success()->title($records->count().' listings approved')->send();
            });
    }

    public static function bulkReject(): BulkAction
    {
        return BulkAction::make('bulkReject')
            ->label('Reject selected')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->schema([self::noteField('Reason shown to the sellers')])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $records->each(fn (Listing $l) => self::applyStatus($l, 'rejected', $data['note']));
                Notification::make()->danger()->title($records->count().' listings rejected')->send();
            });
    }

    public static function bulkBlock(): BulkAction
    {
        return BulkAction::make('bulkBlock')
            ->label('Block selected')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->schema([self::noteField('Internal reason')])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $records->each(fn (Listing $l) => self::applyStatus($l, 'blocked', $data['note']));
                Notification::make()->danger()->title($records->count().' listings blocked')->send();
            });
    }

    public static function bulkFeature(): BulkAction
    {
        return BulkAction::make('bulkFeature')
            ->label('Feature selected')
            ->icon(Heroicon::OutlinedSparkles)
            ->color('warning')
            ->schema([self::daysField()])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $active = $records->where('status', 'active');
                $active->each(fn (Listing $l) => self::applyFeature($l, (int) $data['days']));
                Notification::make()->success()
                    ->title($active->count().' listings featured')
                    ->body($records->count() > $active->count() ? 'Only active listings can be featured.' : null)
                    ->send();
            });
    }

    public static function applyStatus(Listing $listing, string $status, ?string $note = null): void
    {
        $listing->forceFill([
            'status' => $status,
            'moderation_note' => $note,
            'moderated_at' => now(),
            'published_at' => $status === 'active' ? ($listing->published_at ?? now()) : $listing->published_at,
            'is_featured' => $status === 'active' ? $listing->is_featured : false,
        ])->save();
    }

    public static function applyFeature(Listing $listing, int $days): void
    {
        DB::transaction(function () use ($listing, $days): void {
            $start = now();
            $end = $start->copy()->addDays($days);

            $listing->forceFill(['is_featured' => true, 'featured_until' => $end])->save();

            ListingPromotion::query()->create([
                'listing_id' => $listing->id,
                'user_id' => $listing->user_id,
                'package_id' => null,
                'starts_at' => $start,
                'ends_at' => $end,
                'status' => 'active',
            ]);
        });
    }

    public static function removeFeature(Listing $listing): void
    {
        DB::transaction(function () use ($listing): void {
            $listing->forceFill(['is_featured' => false, 'featured_until' => null])->save();

            ListingPromotion::query()
                ->where('listing_id', $listing->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled', 'ends_at' => now()]);
        });
    }

    public static function isFeaturedNow(Listing $listing): bool
    {
        return $listing->is_featured
            && ($listing->featured_until === null || $listing->featured_until->isFuture());
    }

    private static function noteField(string $label): Textarea
    {
        return Textarea::make('note')->label($label)->required()->rows(3)->maxLength(500);
    }

    private static function daysField(): TextInput
    {
        return TextInput::make('days')
            ->label('Duration (days)')
            ->numeric()
            ->minValue(1)
            ->maxValue(90)
            ->default(7)
            ->required();
    }
}
