<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Category::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/category');
        CRUD::setEntityNameStrings('category', 'categories');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */

    public function category_by_sub_category($id){
        return SubCategory::where('id', $id)->get();
//        CRUD::setFromDb();
    }

    protected function setupListOperation()
    {

        CRUD::column('select')
            ->type('select')
            ->entity('subCategory')
            ->attribute('name')
            ->model(Category::class)
            ->wrapper([
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('sub_category/'.$related_key);
                },
            ]);

//        $this->crud->addColumn([
//            'label'          => 'Category',
//            'type'           => 'relationship',
//            'name'           => 'category_id',
//            'entity'         => 'category',
//            'attribute'      => 'name',
//            'wrapper' => [
//                'href' => function ($crud, $column, $entry, $related_key) {
////                    dump($entry);
//                    return backpack_url('sub-category?category_id='.$entry->id);
//                },]
//        ]);
//        CRUD::addColumn([   // select_multiple: n-n relationship (with pivot table)
//            'label'     => 'Category', // Table column heading
//            'type'      => 'relationship',
//            'name'      => 'category', // the method that defines the relationship in your Model
//            'wrapper'   => [
//                'href' => function ($crud, $column, $entry, $related_key) {
//                    return backpack_url('sub-category?category_id='.$entry->getKey());
//                },
//            ],
//        ]);


        $this->crud->addColumn([
            'label' => 'Other',
            'name' => 'action',
            'type' => 'button'
        ]);
        $this->crud->addColumn([
            'label' => 'Slug',
            'name' => 'slug',
        ]);
        $this->crud->addColumn([
            'label' => 'Image',
            'name' => 'image',
            'type' => 'image',
        ]);

        //Badge add in to list
        CRUD::column('checkbox')
            ->type('boolean')
            ->label('Boolean')
            ->options([0 => 'Yes', 1 => 'No'])
            ->wrapper([
                'element' => 'span',
                'class'   => static function ($crud, $column, $entry) {
                    return 'badge badge-'.($entry->{$column['name']} ? 'default' : 'success');
                },
            ]);

        CRUD::column('checkbox')->key('check')->label('Agreed')->type('check');
        CRUD::column('created_at')->type('closure')->label('Created At')->function(function ($entry) {
            return 'Created on '.$entry->created_at;
        });
        $this->crud->addButton('line', 'open_google', 'openGoogle', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'open_google', 'openGoogle', 'beginning');
//        CRUD::setFromDb(); // columns

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CategoryRequest::class);


        $this->crud->addField([
            'label' => 'Image',
            'name' => 'image',
            'type' => 'browse',
        ]);
        CRUD::setFromDb(); // fields

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $setFromDb = $this->crud->get('show.setFromDb');

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // set columns from db
        if ($setFromDb) {
            $this->crud->addColumn([
                'label' => 'Image',
                'name' => 'image',
                'type' => 'image',
            ]);
            $this->crud->setFromDb();
        }

        // cycle through columns
        foreach ($this->crud->columns() as $key => $column) {

            // remove any autoset relationship columns
            if (array_key_exists('model', $column) && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove any autoset table columns
            if ($column['type'] == 'table' && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove the row_number column, since it doesn't make sense in this context
            if ($column['type'] == 'row_number') {
                $this->crud->removeColumn($column['key']);
            }

            // remove columns that have visibleInShow set as false
            if (isset($column['visibleInShow']) && $column['visibleInShow'] == false) {
                $this->crud->removeColumn($column['key']);
            }

            // remove the character limit on columns that take it into account
            if (in_array($column['type'], ['text', 'email', 'model_function', 'model_function_attribute', 'phone', 'row_number', 'select'])) {
                $this->crud->modifyColumn($column['key'], ['limit' => ($column['limit'] ?? 999)]);
            }
        }

        // remove preview button from stack:line
        $this->crud->removeButton('show');

        // remove bulk actions colums
        $this->crud->removeColumns(['blank_first_column', 'bulk_actions']);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getShowView(), $this->data);
    }
}
