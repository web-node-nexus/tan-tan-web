<?php

namespace App\Filament\Resources\Listings\RelationManagers;

use App\Filament\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ConversationsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversations';

    protected static ?string $title = 'Chats';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->conversations()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['buyer', 'seller'])->withCount('messages'))
            ->defaultSort('last_message_at', 'desc')
            ->columns([
                TextColumn::make('buyer.name')->label('Buyer'),
                TextColumn::make('seller.name')->label('Seller'),
                TextColumn::make('messages_count')->label('Messages')->numeric()->badge()->color('gray'),
                TextColumn::make('last_message_at')->label('Last activity')->since()->sortable(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open thread')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Conversation $record): string => ConversationResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No chats yet')
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleLeftRight);
    }
}
