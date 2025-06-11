<!-- Two-column layout -->
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <!-- Left column: All form inputs -->
    <div class="space-y-6">
        <!-- Known Place Name -->
        <flux:input
            name="name"
            label="Name"
            type="text"
            :value="$knownPlace->name ?? old('name')"
            tabindex="0"
            placeholder="Charlotte Manufacturing Plant"
            badge="Required"
            required
            autocomplete="off"
            autofocus
        />

        <!-- Description -->
        <flux:textarea
            name="description"
            label="Description"
            badge="Optional"
            tabindex="0"
            rows="auto"
            x-on:coordinates-updated.window="$el.value = $event.detail[0].formatted_address;"
            placeholder="This is the Charlotte Manufacturing Plant"
        >
            {{ $knownPlace->description ?? old('description') }}
        </flux:textarea>

        <livewire:location-input name="locations" :assignedLocations="$assignedLocations ?? []" />

        <!-- Coordinates -->
        <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 md:gap-4">
            <!-- Latitude -->
            <flux:input
                id="latitude"
                name="latitude"
                :value="$knownPlace->latitude ?? old('latitude', 40.7128)"
                label="Latitude"
                badge="Required"
                x-on:coordinates-updated.window="$el.value = $event.detail[0].latitude;"
                required
                placeholder="40.7128"
                tabindex="0"
            />

            <!-- Longitude -->
            <flux:input
                id="longitude"
                name="longitude"
                :value="$knownPlace->longitude ?? old('longitude', -74.0060)"
                label="Longitude"
                badge="Required"
                x-on:coordinates-updated.window="$el.value = $event.detail[0].longitude"
                required
                placeholder="-74.0060"
                tabindex="0"
            />
        </div>

        <!-- Radius and GPS Accuracy -->
        <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 md:gap-4">
            <!-- Radius (in meters) -->
            <flux:input
                type="number"
                id="radius"
                label="Radius"
                badge="Required"
                name="radius"
                :value="$knownPlace->radius ?? old('radius', 75)"
                required
                min="1"
                tabindex="0"
            />

            <!-- Accuracy -->
            <flux:input
                type="number"
                id="accuracy"
                name="accuracy"
                label="Accuracy"
                :value="$knownPlace->accuracy ?? old('accuracy', 50)"
                badge="Required"
                required
                min="1"
                tabindex="0"
            />
        </div>

        <div id="color-container" class="flex flex-col">
            <flux:input
                name="color"
                type="text"
                label="Color"
                :value="$knownPlace->color ?? old('color', '#3b82f6')"
                id="color"
                tabindex="0"
                badge="Optional"
                data-color
            />
        </div>

        <!-- Group -->
        <flux:field>
            <flux:select label="Group" badge="Optional" name="group_id" placeholder="Choose a group" class="w-full">
                @forelse ($groups as $group)
                    <flux:select.option value="{{ old('group_id', $group->id) }}">
                        {{ $group->name }}
                    </flux:select.option>
                @empty
                    <flux:select.option disabled>No groups created...</flux:select.option>
                @endforelse
            </flux:select>
        </flux:field>

        {{--
            Custom validation order select implementation.
            Using a custom select instead of flux:checkbox.group due to a known issue with
            checkboxes in Flux that prevents proper form submission
        --}}
        <div class="max-w-full md:max-w-3xs">
            <flux:field class="flex items-center justify-between">
                <flux:label for="validation_order" badge="Required">Validation Order</flux:label>
                <select
                    id="validation_order"
                    name="validation_order[]"
                    class="block w-full rounded-md border-zinc-300 text-sm shadow-sm focus:bg-transparent dark:border-zinc-200 dark:text-zinc-300"
                    multiple
                    size="2"
                >
                    <option value="gps" class="px-2 py-1.5 checked:bg-accent-content" selected @selected(old('gps'))>
                        GPS
                    </option>
                    <option value="wifi" class="px-2 py-1.5 checked:bg-accent-content" @selected(old('wifi'))>
                        WiFi
                    </option>
                </select>
                <flux:description>
                    Select one or both validation methods (hold Ctrl/Cmd to select multiple)
                </flux:description>
            </flux:field>
        </div>

        {{-- Form Buttons --}}
        <div class="flex items-center justify-end space-x-3 pt-4">
            <flux:button type="button" variant="filled" class="cursor-pointer">Clear</flux:button>
            <flux:button type="submit" variant="primary" class="cursor-pointer">
                {{ Route::is('known-places.create') ? 'Create' : 'Update' }}
            </flux:button>
        </div>
    </div>

    <!-- Map -->
    <div class="relative h-full">
        <!-- Map container -->
        <div class="h-full rounded-md border border-zinc-200 dark:border-zinc-700">
            <div id="map" class="h-full w-full rounded-md"></div>
        </div>

        <!-- Address search overlay -->
        <livewire:address-search />
    </div>
</div>

<style>
    #validation_order {
        background-color: color-mix(in oklab, var(--color-white) 10%, transparent) !important;
    }

    /* Making sure that the address search field is the proper color regardless of the appearance settings */
    .dark #address_search {
        background-color: color-mix(in srgb, #27272a 100%, var(--color-white) 10%) !important;
    }
</style>
