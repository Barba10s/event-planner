<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ChannelRequest;
use App\Models\Channel\Channel;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ChannelCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup()
    {
        CRUD::setModel(Channel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/channel');
        $this->crud->setEntityNameStrings('канал', 'каналы');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID')->type('number');

        CRUD::column('name')
            ->label('Название')
            ->type('text')
            ->searchable(true);

        CRUD::column('owner')
            ->label('Владелец')
            ->type('select')
            ->entity('owner')
            ->model('App\Models\User')
            ->attribute('name');

        CRUD::column('description')
            ->label('Описание')
            ->type('text')
            ->limit(100);

        CRUD::column('created_at')
            ->label('Создан')
            ->type('datetime');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(ChannelRequest::class);

        CRUD::field('name')
            ->label('Название')
            ->type('text');

        CRUD::field('description')
            ->label('Описание')
            ->type('textarea');

        CRUD::field('owner_id')
            ->label('Владелец')
            ->type('select')
            ->entity('owner')
            ->model('App\Models\User')
            ->attribute('name');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
