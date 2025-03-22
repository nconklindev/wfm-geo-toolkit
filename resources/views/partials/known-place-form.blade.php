<!-- Two-column layout -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Left column: All form inputs -->
    <div class="space-y-6">
        <!-- Known Place Name -->
        <flux:input name="name" label="Name" type="text" :value="$knownPlace->name ?? old('name')" tabindex="0"
                    placeholder="Charlotte Manufacturing Plant" badge="Required" required
                    autofocus/>


        <!-- Description -->
        <flux:textarea name="description" label="Description" badge="Optional" tabindex="0"
                       rows="auto"
                       x-on:coordinates-updated.window="$el.value = $event.detail[0].formatted_address;"
                       placeholder="This is the Charlotte Manufacturing Plant"
                       :value="$knownPlace->description ?? old('description')"/>

        <flux:input name="location_path" badge="Optional" label="Location Path" type="text"
                    :value="$knownPlace->location_path ?? old('location_path')"
                    placeholder="Acme Inc/Acme/Charlotte/Manufacturing Plant"
                    tabindex="0"/>


        <!-- Coordinates and Radius -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Latitude -->
            <flux:input id="latitude" name="latitude" :value="$knownPlace->latitude ?? old('latitude', 40.7128)"
                        label="Latitude" badge="Required"
                        x-on:coordinates-updated.window="$el.value = $event.detail[0].latitude;"
                        class="w-full" required copyable placeholder="e.g. 40.7128" tabindex="0"/>


            <!-- Longitude -->
            <flux:input id="longitude" name="longitude"
                        :value="$knownPlace->longitude ?? old('longitude', -74.0060)"
                        label="Longitude" badge="Required"
                        x-on:coordinates-updated.window="$el.value = $event.detail[0].longitude"
                        class="w-full" required copyable placeholder="e.g. -74.0060" tabindex="0"/>
        </div>

        <!-- Radius and GPS Accuracy -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Radius (in meters) -->
            <flux:field>
                <flux:label badge="Required">Radius (meters)</flux:label>
                <flux:input type="number" id="radius" name="radius" :value="$knownPlace->radius ?? old('radius', 75)"
                            required min="1"/>
            </flux:field>


            <!-- GPS Accuracy Threshold -->
            <flux:field>
                <flux:input type="number" id="gps_accuracy_threshold"
                            name="gps_accuracy_threshold"
                            label="GPS Accuracy Threshold"
                            :value="$knownPlace->gps_accuracy_threshold ?? old('gps_accuracy_threshold', 50)"
                            class="block w-full"
                            badge="Required"
                            required min="1" tabindex="0"/>
            </flux:field>
        </div>

        {{--
            Custom validation order select implementation.
            Using a custom select instead of flux:checkbox.group due to a known issue with
            checkboxes in Flux that prevents proper form submission
        --}}
        <div class="max-w-full md:max-w-3xs">
            <flux:field class="flex items-center justify-between">
                <flux:label for="validation_order" badge="Required">Validation Order</flux:label>
                <select id="validation_order" name="validation_order[]"
                        class="block w-full text-sm rounded-md focus:bg-transparent border-zinc-300 dark:border-zinc-200 shadow-sm
                 dark:text-zinc-300 "
                        multiple
                        size="2">
                    <option value="gps"
                            class="px-2 py-1.5  checked:bg-accent-content"
                            selected @selected(old('gps'))>
                        GPS
                    </option>
                    <option value="wifi"
                            class="px-2 py-1.5  checked:bg-accent-content"
                        @selected(old('wifi'))>
                        WiFi
                    </option>
                </select>
                <flux:description>
                    Select one or both validation methods (hold Ctrl/Cmd to select multiple)
                </flux:description>
            </flux:field>
        </div>


        {{-- Form Buttons --}}
        <div class="pt-4 flex items-center justify-end space-x-3">
            <flux:button type="button" variant="filled" class="cursor-pointer"
            >
                Clear
            </flux:button>
            <flux:button type="submit" variant="primary" class="cursor-pointer">
                {{ Route::is('known-places.create') ? 'Submit' : 'Update' }}
            </flux:button>
        </div>
    </div>

    <!-- Map -->
    <div class="relative h-full">
        <!-- Map container -->
        <div class="rounded-md border border-zinc-200 h-full dark:border-zinc-700">
            <div id="map" class="h-full w-full rounded-md"></div>
        </div>

        <!-- Address search overlay -->
        <livewire:address-search/>
    </div>
</div>
