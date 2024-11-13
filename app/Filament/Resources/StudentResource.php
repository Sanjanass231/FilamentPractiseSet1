<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationLabel = 'Students';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                     Step::make('Personal Informaton')
                        ->schema([
                            TextInput::make('name')->label('Name')->required()
                                ->minLength(3),
                            TextInput::make('student_id')->required(),
                        ])
                        ->description('enter your personal details')
                        ->icon('heroicon-o-user'),
                     Step::make('Address')
                        ->schema([
                            TextInput::make('address_1')->required(),
                            TextInput::make('address_2')->required(),
                        ])->icon('heroicon-o-home')
                        ->description('add your address'),
                     Step::make('School')
                        ->schema([
                           Select::make('standard_id')->required()->relationship('standard', 'name')->label('Standard'),

                        ])->icon('heroicon-o-academic-cap'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('standard.name')->label('Standard')->searchable(),

            ])
            ->filters([
                Filter::make('start')->query(fn (Builder $query): Builder => $query->where('standard_id', 1)),
                SelectFilter::make('standard_id')
                    ->options([
                        1 => 'Standard 1',
                        5 => 'Standard 5',
                        9 => 'Standard 9',
                    ]), SelectFilter::make('All Standard')
                    ->relationship('standard', 'name'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Promote')
                        ->action(function (Student $record) {
                            $record->standard_id = $record->standard_id + 1;
                            $record->save();
                        })
                        ->color('success')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('Demote')
                        ->action(function (Student $record) {
                            if ($record->standard_id > 1) {
                                $record->standard_id = $record->standard_id - 1;
                                $record->save();
                            }
                        })->requiresConfirmation()
                        ->color('danger'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Promote All')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->standard_id += 1;
                                $record->save();
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Name' => $record->name,
            'Standard' => $record->standard->name,
        ];
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('Edit')
                ->iconButton()
                ->icon('heroicon-o-pencil')
                ->url(static::getUrl('edit', ['record' => $record])),
            Action::make('View')
                ->iconButton()
                ->icon('heroicon-s-eye')
                ->url(static::getUrl('index')),
        ];

    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GuardiansRelationManager::class,
        ];
    }
}
