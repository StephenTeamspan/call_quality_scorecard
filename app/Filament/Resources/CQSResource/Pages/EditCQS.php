<?php

namespace App\Filament\Resources\CQSResource\Pages;

use App\Filament\Resources\CQSResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCQS extends EditRecord
{
    protected static string $resource = CQSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('Reset Form')
                ->action(function (){
                    $this->form->fill(); 
                }),
        ];
    }
}
