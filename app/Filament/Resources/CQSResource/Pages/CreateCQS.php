<?php

namespace App\Filament\Resources\CQSResource\Pages;

use App\Filament\Resources\CQSResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateCQS extends CreateRecord
{
    protected static string $resource = CQSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Reset Form')
                ->action(function (){
                    $this->form->fill(); 
                }),
        ];
    }

}
