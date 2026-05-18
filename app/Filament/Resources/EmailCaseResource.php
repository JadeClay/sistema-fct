<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailCaseResource\Pages\CreateEmailCase;
use App\Filament\Resources\EmailCaseResource\Pages\EditEmailCase;
use App\Filament\Resources\EmailCaseResource\Pages\ListEmailCases;
use App\Filament\Resources\EmailCaseResource\Pages\ViewEmailCase;
use App\Models\Category;
use App\Models\EmailCase;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailCaseResource extends Resource
{
    protected static ?string $model = EmailCase::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedEnvelope;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sender_email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                Textarea::make('body')
                    ->label('Body')
                    ->columnSpanFull(),
                Select::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->nullable()
                    ->hidden(fn (): bool => auth()->user()?->hasRole('Agent')),
                TextInput::make('gmail_message_id')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_resolved')
                    ->label('Resolved'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('sender_email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assignee.name')
                    ->label('Assigned to')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_resolved')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('gmail_message_id')
                    ->label('Gmail ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(20),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_resolved')
                    ->label('Resolved'),
                SelectFilter::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->label('Assigned to'),
            ])
            ->recordActions([
                Action::make('replyInGmail')
                    ->label('Reply in Gmail')
                    ->icon(Heroicon::OutlinedLink)
                    ->url(fn (EmailCase $record): string => 'https://mail.google.com/mail/u/0/#search/rfc822msgid:'.$record->gmail_message_id)
                    ->openUrlInNewTab()
                    ->color('success'),
                Action::make('convertToTicket')
                    ->label('Convert to Ticket')
                    ->icon(Heroicon::OutlinedArrowRightOnRectangle)
                    ->color('warning')
                    ->form([
                        Select::make('category_id')
                            ->label('Category')
                            ->options(fn (): array => Category::pluck('name', 'id')->toArray())
                            ->required(),
                        Select::make('status_id')
                            ->label('Status')
                            ->options(fn (): array => Status::pluck('name', 'id')->toArray())
                            ->required(),
                        Select::make('assigned_to')
                            ->label('Assigned to')
                            ->options(fn (): array => User::role(['Super Admin', 'Supervisor', 'Agent'])->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->nullable()
                            ->hidden(fn (): bool => auth()->user()?->hasRole('Agent')),
                    ])
                    ->action(function (array $data, EmailCase $record): void {
                        $ticket = Ticket::create([
                            'title' => $record->subject,
                            'description' => $record->body,
                            'category_id' => $data['category_id'],
                            'status_id' => $data['status_id'],
                            'assigned_to' => $data['assigned_to'],
                            'creator_id' => auth()->id(),
                        ]);

                        $record->update(['is_resolved' => true]);

                        Notification::make()
                            ->title('Ticket created successfully')
                            ->success()
                            ->send();

                        redirect()->to(TicketResource::getUrl('view', ['record' => $ticket]));
                    }),
                EditAction::make(),
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
        $query = parent::getEloquentQuery()->with(['assignee']);

        if (auth()->guest()) {
            return $query;
        }

        $user = auth()->user();

        if ($user->hasRole('Agent')) {
            return $query->where('assigned_to', $user->id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCases::route('/'),
            'create' => CreateEmailCase::route('/create'),
            'view' => ViewEmailCase::route('/{record}'),
            'edit' => EditEmailCase::route('/{record}/edit'),
        ];
    }
}
