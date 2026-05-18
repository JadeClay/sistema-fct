<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages\CreateTicket;
use App\Filament\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Ticket;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Comments\Filament\Infolists\Components\CommentsEntry;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('status_id')
                    ->relationship('status', 'name')
                    ->required(),
                Select::make('creator_id')
                    ->relationship('creator', 'name')
                    ->disabled()
                    ->visible(fn (string $operation): bool => $operation !== 'create'),
                Select::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->nullable()
                    ->hidden(fn (): bool => auth()->user()?->hasRole('Agent')),
                Select::make('last_attended_by')
                    ->relationship('lastAttendee', 'name')
                    ->disabled()
                    ->nullable(),
                CommentsEntry::make('comments')
                    ->visible(fn (?string $operation): bool => $operation !== 'create'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Description')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('description')
                            ->html()
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
                Section::make('Ticket Details')
                    ->schema([
                        TextEntry::make('title')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status.name')
                                    ->badge()
                                    ->color(fn (Ticket $record): string => $record->status?->color ?? 'gray')
                                    ->label('Status'),
                                TextEntry::make('category.name')
                                    ->badge()
                                    ->label('Category'),
                                TextEntry::make('created_at')
                                    ->date()
                                    ->label('Created at')
                                    ->icon(Heroicon::OutlinedCalendarDays),
                                TextEntry::make('creator.name')
                                    ->label('Created by')
                                    ->icon(Heroicon::OutlinedUser),
                                TextEntry::make('assignee.name')
                                    ->label('Assigned to')
                                    ->icon(Heroicon::OutlinedUser),
                                TextEntry::make('lastAttendee.name')
                                    ->label('Last attended by')
                                    ->icon(Heroicon::OutlinedUser),
                            ]),
                    ]),
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        CommentsEntry::make('comments')
                            ->hiddenLabel(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status.name')
                    ->badge()
                    ->color(fn (Ticket $record): string => $record->status?->color ?? 'gray')
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Created by')
                    ->sortable(),
                TextColumn::make('assignee.name')
                    ->label('Assigned to')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('lastAttendee.name')
                    ->label('Last attended by')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status_id')
                    ->relationship('status', 'name')
                    ->label('Status'),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with([
            'category',
            'status',
            'creator',
            'assignee',
            'lastAttendee',
        ]);

        if (auth()->guest()) {
            return $query;
        }

        $user = auth()->user();

        if ($user->hasRole('Agent')) {
            return $query->where(fn (Builder $q) => $q->where('assigned_to', $user->id)
                ->orWhere('creator_id', $user->id)
            );
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }
}
