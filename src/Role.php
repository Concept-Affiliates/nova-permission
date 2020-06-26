<?php

namespace Vyuldashev\NovaPermission;

use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Spatie\Permission\PermissionRegistrar;

class Role extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Spatie\Permission\Models\Role::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        if (auth()->user()->id == 7 || auth()->user()->id == 12) {
            // Andrea (ID: 7) - hide special roles
            /*
            4 = Dev Admin
            5 = Api Access
            6 = Affiliate Admin
            */
            return $query->whereNotIn('id', [4,5,6]); 

        } else {
            // return all roles
            return $query;
        }        
    }

	public static function getModel()
    {
        return app(PermissionRegistrar::class)->getRoleClass();
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __('nova-permission-tool::navigation.sidebar-label');
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(NovaRequest $request)
    {
        //return Gate::allows('viewAny', app(PermissionRegistrar::class)->getRoleClass());

		if (auth()->user()->hasRole('Dev Admin')) {
			return true;
		} else {
			return false;
		}
    }

    public static function label()
    {
        return __('nova-permission-tool::resources.Roles');
    }

    public static function singularLabel()
    {
        return __('nova-permission-tool::resources.Role');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $guardOptions = collect(config('auth.guards'))->mapWithKeys(function ($value, $key) {
            return [$key => $key];
        });

        $userResource = Nova::resourceForModel(getModelForGuard($this->guard_name));

        return [
            ID::make()->sortable(),

            Text::make(__('nova-permission-tool::roles.name'), 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:'.config('permission.table_names.roles'))
                ->updateRules('unique:'.config('permission.table_names.roles').',name,{{resourceId}}'),

            Select::make(__('nova-permission-tool::roles.guard_name'), 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)]),

            DateTime::make(__('nova-permission-tool::roles.created_at'), 'created_at')->exceptOnForms(),
            DateTime::make(__('nova-permission-tool::roles.updated_at'), 'updated_at')->exceptOnForms(),

            PermissionBooleanGroup::make(__('nova-permission-tool::roles.permissions'), 'permissions'),

            MorphToMany::make($userResource::label(), 'users', $userResource)
                ->searchable()
                ->singularLabel($userResource::singularLabel()),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
