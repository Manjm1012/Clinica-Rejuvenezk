<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Doctor;
use App\Models\User;
use App\Support\AdminClinic;
use App\Support\MedicalPanel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Usuarios del equipo de la clínica: administradores, recepción y médicos.
 * Un médico se vincula a su perfil de Doctor para entrar al panel /medico.
 */
class UserResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Usuarios y roles';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'usuarios';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('doctor')
            ->where('clinic_id', AdminClinic::idOrNone());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de acceso')->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(200),
                Forms\Components\TextInput::make('email')->label('Correo')->email()->required()->maxLength(200)
                    ->unique(User::class, 'email', ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label(fn (string $operation) => $operation === 'create' ? 'Contraseña' : 'Nueva contraseña')
                    ->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->minLength(10)->maxLength(255)
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText('Mínimo 10 caracteres. En edición, déjala vacía para no cambiarla.'),
                Forms\Components\Select::make('role')->label('Rol')
                    ->options(fn () => MedicalPanel::enabled() ? User::ROLES : array_diff_key(User::ROLES, [User::ROLE_DOCTOR => true]))->required()->default(User::ROLE_RECEPTION)->live()
                    ->helperText(fn () => 'Administrador: todo el panel. Recepción: leads, citas, pacientes y solicitudes.'.(MedicalPanel::enabled() ? ' Médico: solo el panel médico.' : '')),
            ])->columns(2),

            Forms\Components\Section::make('Perfil de médico')
                ->visible(fn () => MedicalPanel::enabled())
                ->description('Vincula la cuenta con el perfil del médico (Contenido del sitio → Doctores) para que pueda entrar a /medico. Un administrador que también atiende pacientes puede tener perfil.')
                ->schema([
                    Forms\Components\Select::make('doctor_profile_id')
                        ->label('Perfil de médico')
                        ->options(fn (?User $record) => Doctor::query()
                            ->withoutGlobalScope('clinic')
                            ->where('clinic_id', AdminClinic::idOrNone())
                            ->where(fn (Builder $q) => $q->whereNull('user_id')->when($record, fn (Builder $q) => $q->orWhere('user_id', $record->getKey())))
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required(fn (Get $get) => $get('role') === User::ROLE_DOCTOR)
                        ->dehydrated(false)
                        ->afterStateHydrated(fn (Forms\Components\Select $component, ?User $record) => $component->state($record?->doctor?->getKey())),
                ]),
        ]);
    }

    /** Con el panel médico apagado no se puede asignar el rol Médico (aunque se manipule la petición). */
    public static function guardRole(?string $role): void
    {
        if (! array_key_exists((string) $role, User::ROLES) || ($role === User::ROLE_DOCTOR && ! MedicalPanel::enabled())) {
            throw ValidationException::withMessages(['data.role' => 'Rol no válido.']);
        }
    }

    /** Vincula (o desvincula) el perfil de médico elegido con la cuenta. */
    public static function syncDoctorProfile(User $user, mixed $doctorId): void
    {
        if (! MedicalPanel::enabled()) {
            return;
        }

        Doctor::query()->withoutGlobalScope('clinic')
            ->where('user_id', $user->getKey())
            ->when($doctorId, fn (Builder $q) => $q->whereKeyNot($doctorId))
            ->update(['user_id' => null]);

        if ($doctorId) {
            Doctor::query()->withoutGlobalScope('clinic')
                ->whereKey($doctorId)
                ->where('clinic_id', $user->clinic_id)
                ->update(['user_id' => $user->getKey()]);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Correo')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('Rol')->badge()
                    ->formatStateUsing(fn (?string $state, User $record) => $record->is_super_admin ? 'Super admin' : (User::ROLES[$state] ?? $state))
                    ->color(fn (?string $state) => match ($state) {
                        User::ROLE_ADMIN => 'warning',
                        User::ROLE_DOCTOR => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('doctor.name')->label('Perfil médico')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Creado')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->label('Rol')->options(User::ROLES),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (User $record) => ! $record->is_super_admin || auth()->user()?->is_super_admin),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record) => $record->isNot(auth()->user()) && ! $record->is_super_admin),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        return ! $record->is_super_admin || (bool) auth()->user()?->is_super_admin;
    }

    public static function canDelete(Model $record): bool
    {
        return $record->isNot(auth()->user()) && ! $record->is_super_admin;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
