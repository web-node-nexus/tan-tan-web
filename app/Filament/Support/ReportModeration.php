<?php

namespace App\Filament\Support;

use App\Models\Report;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportModeration
{
    public const STATUS_LABELS = [
        'open' => 'Open',
        'reviewing' => 'In review',
        'resolved' => 'Resolved',
        'dismissed' => 'Dismissed',
    ];

    public const REASON_LABELS = [
        'spam' => 'Spam',
        'fraud' => 'Fraud / scam',
        'wrong_category' => 'Wrong category',
        'sold_elsewhere' => 'Sold elsewhere',
        'other' => 'Other',
    ];

    public static function statusColor(?string $status): string
    {
        return match ($status) {
            'open' => 'danger',
            'reviewing' => 'warning',
            'resolved' => 'success',
            default => 'gray',
        };
    }

    public static function reasonLabel(?string $reason): string
    {
        return self::REASON_LABELS[$reason] ?? str($reason ?? '')->replace('_', ' ')->title()->toString();
    }

    public static function startReview(): Action
    {
        return Action::make('startReview')
            ->label('Start review')
            ->icon(Heroicon::OutlinedEye)
            ->color('warning')
            ->visible(fn (Report $record): bool => $record->status === 'open')
            ->action(function (Report $record): void {
                $record->update(['status' => 'reviewing']);
                Notification::make()->success()->title('Report moved to review')->send();
            });
    }

    public static function resolve(): Action
    {
        return Action::make('resolve')
            ->label('Resolve')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Report $record): bool => in_array($record->status, ['open', 'reviewing'], true))
            ->modalHeading('Resolve report')
            ->schema([
                Textarea::make('note')->label('Moderator note')->required()->rows(3)->maxLength(1000),
                Toggle::make('block_listing')->label('Also block the reported listing')->default(true),
            ])
            ->action(function (Report $record, array $data): void {
                self::close($record, 'resolved', $data['note'], (bool) $data['block_listing']);
                Notification::make()->success()->title('Report resolved')->send();
            });
    }

    public static function dismiss(): Action
    {
        return Action::make('dismiss')
            ->label('Dismiss')
            ->icon(Heroicon::OutlinedXMark)
            ->color('gray')
            ->visible(fn (Report $record): bool => in_array($record->status, ['open', 'reviewing'], true))
            ->schema([
                Textarea::make('note')->label('Why is this not actionable?')->required()->rows(3)->maxLength(1000),
            ])
            ->action(function (Report $record, array $data): void {
                self::close($record, 'dismissed', $data['note'], false);
                Notification::make()->success()->title('Report dismissed')->send();
            });
    }

    public static function bulkResolve(): BulkAction
    {
        return BulkAction::make('bulkResolve')
            ->label('Resolve selected')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->schema([
                Textarea::make('note')->required()->rows(3)->maxLength(1000),
                Toggle::make('block_listing')->label('Block reported listings')->default(false),
            ])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $records->each(fn (Report $r) => self::close($r, 'resolved', $data['note'], (bool) $data['block_listing']));
                Notification::make()->success()->title($records->count().' reports resolved')->send();
            });
    }

    public static function bulkDismiss(): BulkAction
    {
        return BulkAction::make('bulkDismiss')
            ->label('Dismiss selected')
            ->icon(Heroicon::OutlinedXMark)
            ->color('gray')
            ->schema([
                Textarea::make('note')->required()->rows(3)->maxLength(1000),
            ])
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records, array $data): void {
                $records->each(fn (Report $r) => self::close($r, 'dismissed', $data['note'], false));
                Notification::make()->success()->title($records->count().' reports dismissed')->send();
            });
    }

    public static function close(Report $report, string $status, string $note, bool $blockListing): void
    {
        DB::transaction(function () use ($report, $status, $note, $blockListing): void {
            $report->update([
                'status' => $status,
                'admin_note' => $note,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            if ($blockListing && $report->listing && $report->listing->status !== 'blocked') {
                ListingModeration::applyStatus($report->listing, 'blocked', 'Report #'.$report->id.': '.$note);
            }
        });
    }
}
