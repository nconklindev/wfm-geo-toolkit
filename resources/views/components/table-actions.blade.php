@props([
    /**@var\Illuminate\Database\Eloquent\Model*/'model',
    /**@varstring|null*/'resourceName' => null,
    /**@varstring|null*/'showRoute' => null,
    /**@varstring|null*/'editRoute' => null,
    /**@varstring|null*/'deleteRoute' => null,
    /**@varbool*/'showView' => true,
    /**@varbool*/'showEdit' => true,
    /**@varbool*/'showDelete' => true,
])

<div {{ $attributes->class(['flex space-x-2']) }}>
    @if ($showView && ($resourceName || $showRoute))
        <a href="{{ $showRoute ?? route($resourceName . '.show', $model) }}" x-on:click="event.stopPropagation()">
            <flux:icon.eye
                class="h-5 w-5 cursor-pointer text-indigo-600 hover:text-indigo-800 dark:text-indigo-500 hover:dark:text-indigo-300"
            />
        </a>
    @endif

    @if ($showEdit && ($resourceName || $editRoute))
        <a href="{{ $editRoute ?? route($resourceName . '.edit', $model) }}" x-on:click="event.stopPropagation()">
            <flux:icon.pencil-square
                class="h-5 w-5 cursor-pointer text-blue-600 hover:text-blue-800 dark:text-blue-500 hover:dark:text-blue-300"
            />
        </a>
    @endif

    @if ($showDelete && ($resourceName || $deleteRoute))
        <flux:modal.trigger
            name="{{ 'delete-' . Str::plural(Str::kebab(class_basename($model))) . '-' . $model->id }}"
        >
            <flux:icon.trash
                class="h-5 w-5 cursor-pointer text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-300"
            />
        </flux:modal.trigger>
        <x-delete-confirmation-modal :model="$model" :item-to-delete="$model->name" />
    @endif
</div>
