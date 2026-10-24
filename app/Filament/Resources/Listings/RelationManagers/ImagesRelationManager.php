<?php

namespace App\Filament\Resources\Listings\RelationManagers;

use App\Models\ListingImage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Photos';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedPhoto;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->images()->count();
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label('Photo')
                    ->state(fn (ListingImage $record): string => $record->url())
                    ->imageSize(72)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                IconColumn::make('is_cover')->label('Cover')->boolean()->trueIcon(Heroicon::Star)->trueColor('warning'),
                TextColumn::make('sort_order')->label('Position')->numeric(),
                TextColumn::make('created_at')->label('Uploaded')->since(),
            ])
            ->recordActions([
                Action::make('makeCover')
                    ->label('Set as cover')
                    ->icon(Heroicon::OutlinedStar)
                    ->color('warning')
                    ->visible(fn (ListingImage $record): bool => ! $record->is_cover)
                    ->action(function (ListingImage $record): void {
                        $record->listing->images()->update(['is_cover' => false]);
                        $record->update(['is_cover' => true]);
                        Notification::make()->success()->title('Cover photo updated')->send();
                    }),
                DeleteAction::make()->modalDescription('Removes this photo from the ad. The seller will not be notified.'),
            ])
            ->emptyStateHeading('No photos')
            ->emptyStateIcon(Heroicon::OutlinedPhoto);
    }
}
