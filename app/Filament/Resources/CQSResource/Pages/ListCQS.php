<?php

namespace App\Filament\Resources\CQSResource\Pages;

use App\Filament\Resources\CQSResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCQS extends ListRecords
{
    protected static string $resource = CQSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
