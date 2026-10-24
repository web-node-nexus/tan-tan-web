<?php

namespace App\Filament\Resources\Conversations;

use App\Filament\Resources\Conversations\Pages\ListConversations;
use App\Filament\Resources\Conversations\Pages\ViewConversation;
use App\Filament\Resources\Conversations\RelationManagers\OffersRelationManager;
use App\Filament\Resources\Listings\ListingResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Conversation;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Trust & safety';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Chats';

    protected static ?string $modelLabel = 'chat';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Thread')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->description('Read-only audit view. Messages are shown exactly as exchanged.')
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        ViewEntry::make('messages')
                            ->hiddenLabel()
                            ->view('filament.infolists.chat-transcript'),
                    ]),
                Group::make([
                    Section::make('Listing')
                        ->icon(Heroicon::OutlinedRectangleStack)
                        ->schema([
                            TextEntry::make('listing.title')->hiddenLabel()->weight(FontWeight::SemiBold)
                                ->url(fn (Conversation $record): ?string => $record->listing ? ListingResource::getUrl('view', ['record' => $record->listing]) : null)
                                ->color('primary'),
                            TextEntry::make('listing.price')->label('Price')->money('INR', locale: 'en_IN', decimalPlaces: 0),
                        ]),
                    Section::make('Participants')
                        ->icon(Heroicon::OutlinedUsers)
                        ->schema([
                            TextEntry::make('buyer.name')->label('Buyer')
                                ->url(fn (Conversation $record): ?string => $record->buyer ? UserResource::getUrl('view', ['record' => $record->buyer]) : null)
                                ->color('primary'),
                            TextEntry::make('seller.name')->label('Seller')
                                ->url(fn (Conversation $record): ?string => $record->seller ? UserResource::getUrl('view', ['record' => $record->seller]) : null)
                                ->color('primary'),
                            TextEntry::make('created_at')->label('Started')->dateTime('d M Y, H:i'),
                            TextEntry::make('last_message_at')->label('Last message')->since()->placeholder('—'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['listing', 'buyer', 'seller'])->withCount(['messages', 'offers']))
            ->columns([
                TextColumn::make('listing.title')->label('Listing')->searchable()->limit(36)->weight(FontWeight::SemiBold),
                TextColumn::make('buyer.name')->label('Buyer')->searchable(),
                TextColumn::make('seller.name')->label('Seller')->searchable(),
                TextColumn::make('messages_count')->label('Messages')->numeric()->sortable()->badge()->color('gray'),
                TextColumn::make('offers_count')->label('Offers')->numeric()->sortable()->badge()->color('warning'),
                TextColumn::make('last_message_at')->label('Last activity')->since()->dateTimeTooltip('d M Y, H:i')->sortable(),
                TextColumn::make('created_at')->label('Started')->date('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('with_offers')->label('Has offers')->toggle()->query(fn (Builder $query): Builder => $query->has('offers')),
                Filter::make('recent')->label('Active in last 24h')->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('last_message_at', '>=', now()->subDay())),
            ])
            ->recordActions([ViewAction::make()])
            ->striped()
            ->defaultSort('last_message_at', 'desc')
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleLeftRight)
            ->emptyStateHeading('No chats yet');
    }

    public static function getRelations(): array
    {
        return [
            OffersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConversations::route('/'),
            'view' => ViewConversation::route('/{record}'),
        ];
    }
}
