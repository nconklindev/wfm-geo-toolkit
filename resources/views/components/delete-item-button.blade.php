<flux:button
    type="submit"
    variant="danger"
    icon="trash"
    {{ $attributes->class(['cursor-pointer tracking-wider uppercase transition']) }}
    @click="confirm('Are you sure you want to delete?')"
>
    Delete
</flux:button>
