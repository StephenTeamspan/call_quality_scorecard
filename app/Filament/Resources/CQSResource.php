<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CQSResource\Pages;
use App\Filament\Resources\CQSResource\RelationManagers;
use App\Models\CQS;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Component as Livewire;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

class CQSResource extends Resource
{
    protected static ?string $model = CQS::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Call Quality Form';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Section::make('Details')->schema([
                    Select::make('LOB')->label('LOB')->options([
                        'Emergency Repair Group'
                    ]),
                    Select::make('employee_name')->label('Name'),
                    DatePicker::make('date_of_recording')->label('Date of Recording'),
                    TextInput::make('workorder')->label('Workorder #'),
                    TextInput::make('auditor')->label('Auditor')->default(Auth::user()->name)->extraInputAttributes(['readonly' => true]),
                    TextInput::make('audit_date')->label('Audit Date')->default(now())->extraInputAttributes(['readonly' => true]),
                    DatePicker::make('date_processed')->label('Date Processed'),
                    TimePicker::make('time_processed')->label('Time Processed'),
                    Select::make('type_of_call')->label('Type of Call')->options([
                        'Technician' => 'Technician',
                        'Store' => 'Store'
                    ]),
                ])->columnSpan(1),

                Section::make('Remarks')->schema([
                    Textarea::make('call_summary')->label('Call Summary'),
                    Textarea::make('strenghts')->label('Strenght/s'),
                    Textarea::make('opportunities')->label('Opportunities'),
                    Section::make('Call Recording')->schema([
                        FileUpload::make('call_recording')->label('Upload here')
                            ->visibility('public')
                            ->openable()
                            ->downloadable()
                            ->preserveFilenames()
                            ->acceptedFileTypes(['audio/mpeg']) // Set the accepted MIME types for MP3 files
                            ->maxSize(20000) 
                            ->directory('audio_files') // Specify the directory to store the uploaded files
                            ->disk('public')
                    ])->columnSpan(1),
                ])->columnSpan(1),
                
                Section::make('Scorecard')
                ->headerActions([
                    Action::make('Points')->label('Table of Corrseponding Pts')
                        ->modalDescription('The following')
                        ->link()
                        ->icon('heroicon-o-code-bracket-square')
                        ->form(function () {
                            $OpenandCloseSpiel = [
                                'Proper use of opening spiel - Name and Branding' => '3 pts',
                                'Proper use of opening spiel - Compliance - Recorded Line' => '5 pts',
                                'Proper use of opening spiel - Thank you and Goodbye' => '2 pts',
                            ];

                            $CustomerExp = [
                                'Active Listening / Comprehension / Communication' => '10 pts',
                                'Professionalism' => '20 pts',
                            ];

                            $ProceduralAdh= [
                                'Proper Probing' => '20 pts',
                                'Process Mastery' => '20 pts',
                                'Sense of Urgency' => '20 pts',
                                'Accuracy' => '10 pts'
                            ];

                            return [
                                KeyValue::make('Opening and Closing Spiel')
                                    ->addable(false)
                                    ->deletable(false)
                                    ->keyLabel('Subcategory')
                                    ->valueLabel('Points')
                                    ->editableKeys(false)
                                    ->editableValues(false)
                                    ->default($OpenandCloseSpiel), // Pass the associative array directly as default values

                                KeyValue::make('Customer Experience')
                                    ->addable(false)
                                    ->deletable(false)
                                    ->keyLabel('Subcategory')
                                    ->valueLabel('Points')
                                    ->editableKeys(false)
                                    ->editableValues(false)
                                    ->default($CustomerExp), // Pass the associative array directly as default values

                                KeyValue::make('Procedural Adherence')
                                    ->addable(false)
                                    ->deletable(false)
                                    ->keyLabel('Subcategory')
                                    ->valueLabel('Points')
                                    ->editableKeys(false)
                                    ->editableValues(false)
                                    ->default($ProceduralAdh), // Pass the associative array directly as default values
                            ];
                        })
                        ->slideOver()
                        ->modalCancelActionLabel('< Back')
                        ->modalSubmitAction(false),
                ])
                ->schema([
                    TextInput::make('score')->label('Score')->live()->default(100)->numeric()->extraInputAttributes(['readOnly' => true]),
                    Repeater::make('CTQ')->label('Critical to Quality')->schema([
                        Select::make('category')->options([
                            'Opening and Closing Spiel' => 'Opening and Closing Spiel',
                            'Customer Experience' => 'Customer Experience',
                            'Procedural Adherance' => 'Procedural Adherance',
                        ])
                            ->live()
                            ->afterStateUpdated(function (Livewire $livewire) {
                                $livewire->reset('data.sub_category');
                        }),
                        Select::make('sub_category')->options(fn (Get $get): array => match ($get('category')){
                            'Opening and Closing Spiel' => [
                                'Proper use of openning spiel - Name and Branding' =>'Proper use of openning spiel - Name and Branding',
                                'Proper use of openning spiel - Compliance - Recorded Line' =>'Proper use of openning spiel - Compliance - Recorded Line',
                                'Proper use of openning spiel - Thank you and Goodbye' =>'Proper use of openning spiel - Thank you and Goodbye',
                            ],
                            'Customer Experience' => [
                                'Active Listening/ Comprehension/ Communication' => 'Active Listening/ Comprehension/ Communication',
                                'Accuracy' => 'Accuracy',
                            ],
                            'Procedural Adherance' => [
                                'Proper Probing' => 'Proper Probing',
                                'Process Mastery' => 'Process Mastery',
                                'Sense of Urgency' => 'Sense of Urgency',
                                'Accuracy' => 'Accuracy',
                            ],
                            default => [],
                        })
                    ])
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::updateTotal($get, $set);
                    })
                    ->live()
                ])->columnSpan(2),



            ])
            ->columns(2);
    }

    public static function updateTotal(Get $get, Set $set){

        $subcategory = [
            'Proper use of openning spiel - Name and Branding' => 3,
            'Proper use of openning spiel - Compliance - Recorded Line' => 5,
            'Proper use of openning spiel - Thank you and Goodbye' => 2,
            'Active Listening/ Comprehension/ Communication'=> 10,
            'Accuracy' => 20,
            'Proper Probing' => 20,
            'Process Mastery' => 20,
            'Sense of Urgency' => 10,
            'Accuracy' => 10,
        ];  

        $total = 0;

        // Retrieve all selected subcategories
        $CTQ = collect($get('CTQ'));
        $selectedsubcategory = $CTQ->pluck('sub_category');
        $count = $CTQ->count();

        for ($i = 0; $count > $i ; $i++){
            $text = $selectedsubcategory->get($i);
            
            if (array_key_exists($text, $subcategory)) {
                $total += $subcategory[$text];
            }
        }

        $set('score', number_format(100 - $total));
    }



    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                function (CQS $record){
                    return Pages\ViewCQS::getUrl([$record->id]);
                }
            )
            ->columns([
                TextColumn::make('employee_name')->label('Name'),
                TextColumn::make('LOB')->label('LOB'),
                TextColumn::make('date_of_recording')->label('Date of Recording'),
                TextColumn::make('workorder')->label('Workorder #'),
                TextColumn::make('type_of_call')->label('Type of Call'),
                TextColumn::make('date_processed')->label('Date Processed'),
                TextColumn::make('time_processed')->label('Time Processed'),
                TextColumn::make('score')->label('Score'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            InfolistSection::make('Details')->schema([
                TextEntry::make('employee_name')->label('Name'),
                TextEntry::make('LOB')->label('LOB'),
                TextEntry::make('date_of_recording')->label('Date of Recording'),
                TextEntry::make('workorder')->label('Workorder #'),
                TextEntry::make('auditor')->label('Auditor'),
                TextEntry::make('audit_date')->label('Audit Date'),
                TextEntry::make('date_processed')->label('Date Processed'),
                TextEntry::make('time_processed')->label('Time Processed'),
                TextEntry::make('type_of_call')->label('Type of Call')
            ])->columnSpan(1),

            InfolistSection::make('Remarks')->schema([
                TextEntry::make('call_summary')->label('call_summary'),
                TextEntry::make('strenghts')->label('Strenght/s'),
                TextEntry::make('opportunities')->label('Opportunities'),
                InfolistSection::make('Call Recording')->schema([
                    TextEntry::make('call_recording')->label('Upload here')
                ])
            ])->columnSpan(1),

            InfolistSection::make('Scorecard')->schema([
                TextEntry::make('score')->label('Score'),
                RepeatableEntry::make('CTQ')->label('Critical to Quality')->schema([
                    TextEntry::make('category'),
                    TextEntry::make('sub_category')
                ])
            ])
            
            
        ]);
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCQS::route('/'),
            'create' => Pages\CreateCQS::route('/create'),
            'edit' => Pages\EditCQS::route('/{record}/edit'),
            'view' => Pages\ViewCQS::route('/{record}'),
        ];
    }
}
