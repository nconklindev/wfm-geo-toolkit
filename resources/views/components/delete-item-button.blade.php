<flux:button
    type="submit"
    variant="danger"
    size="sm"
    icon="trash"
    {{ $attributes->class(['cursor-pointer transition']) }}
    @click="confirm('Are you sure you want to delete?')"
>
    Delete
</flux:button>
