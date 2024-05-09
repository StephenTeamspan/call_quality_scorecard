<?php

namespace App\Filament\Resources\CQSResource\Pages;

use App\Filament\Resources\CQSResource;
use App\Models\CQS;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewCQS extends ViewRecord
{
    protected static string $resource = CQSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Action::make('View Status')
            ->fillForm(fn (CQS $record): array => [
                'status' => $record->status,
                'comments' => $record->comments,
            ])
            ->form([
                Select::make('status')->label('Status')->options([
                    'Pending' => 'Pending',
                    'Disputed' => 'Disputed',
                    'Acknowledged' => 'Acknowledged'
                ]),
                Textarea::make('comments')->label('Comments'),

            ])
            ->action(function(array $data, CQS $record){

                $record->update([
                    'status' => $data['status'],
                    'comments' => $data['comments'],
                ]);
                $record->save([]);
            }),
        ];
    }
}
