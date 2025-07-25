<div class="mx-auto max-w-4xl space-y-8">
    <!-- Header -->
    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
        <div class="flex space-y-4">
            <div>
                <h1 class="flex text-3xl font-bold text-teal-700 dark:text-teal-400">WFM API Explorer Documentation</h1>
                <p class="mt-3 text-lg text-zinc-600 dark:text-zinc-300">
                    Learn how to set up WFM API access and use the API Explorer tool.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="rounded-lg bg-white p-8 shadow-sm dark:bg-zinc-800">
        <div class="space-y-8">
            <!-- Getting Started Section -->
            <div>
                <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    Getting Started with WFM API Access
                </h2>

                <flux:text class="mb-6 text-sm leading-relaxed md:text-base">
                    Before using the API Explorer, API access will need to be configured in whichever tenant you plan on
                    running the APIs against. You will need to have:
                    <ul class="mt-4 list-inside list-disc">
                        <li>A user with the proper access created in the application</li>
                        <li>An AuthN Client created that uses the Interactive/Password flow and grant type</li>
                    </ul>
                </flux:text>
            </div>

            <!-- Step 1 -->
            <div class="space-y-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    Step 1: Create an API Client in WFM
                </h3>

                <flux:text class="mb-4 text-sm md:text-base">
                    You'll need administrator access to your WFM system to create an API client. Follow these steps:
                </flux:text>

                <div class="rounded-lg bg-zinc-50 p-6 dark:bg-zinc-900/50">
                    <ol class="space-y-3 text-zinc-700 dark:text-zinc-300">
                        <li class="flex items-start">
                            <x-ui.step-item number="1" text="Log into your WFM tenant" />
                        </li>
                        <li class="flex items-center">
                            <x-ui.step-item number="2">
                                <x-ui.step-item.content>
                                    Navigate to
                                    <strong>Administration</strong>
                                    <flux:icon.arrow-right class="inline size-4 stroke-3" />
                                    {{-- stroke-3 seems to be equivalent to <strong> --}}
                                    <strong>Application Setup</strong>
                                    <flux:icon.arrow-right class="inline size-4 stroke-3" />
                                    <strong>Common Setup</strong>
                                    <flux:icon.arrow-right class="inline size-4 stroke-3" />
                                    <strong>Client Management</strong>
                                </x-ui.step-item.content>
                            </x-ui.step-item>
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="3" text="Click the 'Create' button" />
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="4">
                                <x-ui.step-item.content>
                                    <span class="mb-2 block">Fill in the client details:</span>
                                    <ul class="ml-4 space-y-2 text-sm">
                                        <x-ui.list-bullet>
                                            <x-ui.list-bullet.content>
                                                <strong class="text-zinc-900 dark:text-zinc-100">Name:</strong>
                                                Something descriptive like "API Explorer Tool"
                                            </x-ui.list-bullet.content>
                                        </x-ui.list-bullet>
                                        <x-ui.list-bullet>
                                            <x-ui.list-bullet.content>
                                                <strong class="text-zinc-900 dark:text-zinc-100">
                                                    Application Flow:
                                                </strong>
                                                Select "Interactive"
                                            </x-ui.list-bullet.content>
                                        </x-ui.list-bullet>
                                        <x-ui.list-bullet>
                                            <x-ui.list-bullet.content>
                                                <strong class="text-zinc-900 dark:text-zinc-100">Grant Type:</strong>
                                                Select "Password Flow (ROPC)"
                                            </x-ui.list-bullet.content>
                                        </x-ui.list-bullet>
                                    </ul>
                                </x-ui.step-item.content>
                            </x-ui.step-item>
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="5" text="Save" />
                        </li>
                    </ol>
                </div>

                <flux:callout icon="exclamation-triangle" variant="warning">
                    <flux:callout.heading>Important</flux:callout.heading>
                    <!-- prettier-ignore -->
                    <flux:callout.text>
                        After creating the client, you'll receive a
                        <strong>Client ID</strong>, <strong>Client Secret</strong>, <strong>Realm</strong>, and
                        <strong>Organization ID</strong>. If the <strong>Client Secret</strong> does not appear, click
                        the "Refresh" button. Once it generates, save it to a safe place. <strong>It will not be
                            generated again</strong>.
                    </flux:callout.text>
                </flux:callout>
            </div>

            <!-- Step 2 -->
            <div class="space-y-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    Step 2: Create or Configure an API User
                </h3>

                <flux:text class="mb-4 text-sm md:text-base">
                    You'll also need a WFM user account with API access permissions:
                </flux:text>

                <div class="rounded-lg bg-zinc-50 p-6 dark:bg-zinc-900/50">
                    <ol class="space-y-3 text-zinc-700 dark:text-zinc-300">
                        <li class="flex items-start">
                            <x-ui.step-item
                                number="1"
                                color="green"
                                text="Create a new user account or duplicate an existing one"
                            />
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="2" color="green">
                                <x-ui.step-item.content>
                                    <span class="mb-2 block">Assign appropriate access profiles and permissions</span>
                                    <ul class="ml-4 space-y-2 text-sm">
                                        <x-ui.list-bullet>
                                            <x-ui.list-bullet.content>
                                                <strong class="text-zinc-900 dark:text-zinc-100">
                                                    Function Access Profile:
                                                </strong>
                                                Super Access
                                                <sup><strong>*</strong></sup>
                                            </x-ui.list-bullet.content>
                                        </x-ui.list-bullet>
                                        <x-ui.list-bullet>
                                            <x-ui.list-bullet.content>
                                                <strong class="text-zinc-900 dark:text-zinc-100">
                                                    Roles (Edit Licenses):
                                                </strong>
                                                Manager, Employee
                                            </x-ui.list-bullet.content>
                                        </x-ui.list-bullet>
                                        <x-ui.list-bullet>
                                            <x-ui.list-bullet.content>
                                                <strong class="text-zinc-900 dark:text-zinc-100">Licenses:</strong>
                                                Select a license for each product available (i.e. Hourly Timekeeping,
                                                Absence, Advanced Scheduling, etc.)
                                            </x-ui.list-bullet.content>
                                        </x-ui.list-bullet>
                                    </ul>
                                </x-ui.step-item.content>
                            </x-ui.step-item>
                        </li>
                    </ol>
                </div>
            </div>

            <flux:callout icon="information-circle" color="blue">
                <flux:callout.heading>Note</flux:callout.heading>
                <flux:callout.text>
                    <strong>Super Access</strong>
                    is suggested above since it is a system default profile that contains access to everything an API
                    user could want or need access to. If it is company policy for an API user to not have access to
                    everything, review this Function Access Profile and slowly remove access as needed.
                </flux:callout.text>
            </flux:callout>

            <!-- Step 3 -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    Step 3: Gather Required Information
                </h3>

                <flux:text class="text-zinc-700 dark:text-zinc-300">
                    Once you have your API client and user set up, collect the following information:
                </flux:text>

                <div class="grid gap-6 md:grid-cols-2">
                    <x-ui.card>
                        <div class="mb-4 flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Client Configuration</h4>
                            <flux:icon.key class="h-5 w-5 text-blue-500" />
                        </div>

                        <div class="space-y-4">
                            <div class="border-l-4 border-blue-200 pl-4 dark:border-blue-800">
                                <dt class="mb-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Client ID</dt>
                                <dd class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    Generated when you created the API client
                                </dd>
                                <code class="rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                    Example: abc123def456
                                </code>
                            </div>
                            <div class="border-l-4 border-blue-200 pl-4 dark:border-blue-800">
                                <dt class="mb-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    Client Secret
                                </dt>
                                <dd class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    Secret key generated with the client
                                </dd>
                                <code class="rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                    Example: xyz789uvw012
                                </code>
                            </div>
                            <div class="border-l-4 border-blue-200 pl-4 dark:border-blue-800">
                                <dt class="mb-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                    Organization ID
                                </dt>
                                <dd class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    Your WFM organization identifier
                                </dd>
                                <code class="rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                    Example: org_PGHKngyxtxV6kU7Z
                                </code>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card>
                        <div class="mb-4 flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">WFM User Account</h4>
                            <flux:icon.user class="h-5 w-5 text-green-500" />
                        </div>

                        <div class="space-y-4">
                            <div class="border-l-4 border-green-200 pl-4 dark:border-green-800">
                                <dt class="mb-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Username</dt>
                                <dd class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">Your API user's username</dd>
                                <code class="rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                    Example: APIUSER
                                </code>
                            </div>
                            <div class="border-l-4 border-green-200 pl-4 dark:border-green-800">
                                <dt class="mb-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Password</dt>
                                <dd class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">Your API user's password</dd>
                                <code class="rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                    Keep this secure
                                </code>
                            </div>
                            <div class="border-l-4 border-green-200 pl-4 dark:border-green-800">
                                <dt class="mb-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Hostname</dt>
                                <dd class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">Your WFM instance URL</dd>
                                <code class="rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-800">
                                    Example: https://host.prd.mykronos.com
                                </code>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="space-y-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Step 4: Using the API Explorer</h3>

                <p class="mb-4 text-zinc-700 dark:text-zinc-300">
                    With your credentials ready, you can now use the API Explorer:
                </p>

                <div class="rounded-lg bg-zinc-50 p-6 dark:bg-zinc-900/50">
                    <ol class="space-y-4 text-zinc-700 dark:text-zinc-300">
                        <li class="flex items-start">
                            <x-ui.step-item number="1" color="violet" size="lg">
                                <strong class="text-zinc-900 dark:text-zinc-100">Open the API Explorer</strong>
                                <span class="mt-1 block text-sm">Navigate to the API Explorer tool</span>
                            </x-ui.step-item>
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="2" color="violet" size="lg">
                                <strong class="text-zinc-900 dark:text-zinc-100">Enter Credentials</strong>
                                <span class="mt-1 block text-sm">
                                    Fill in all the fields in the "Global Configuration" section
                                </span>
                            </x-ui.step-item>
                            <div></div>
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="3" color="violet" size="lg">
                                <strong class="text-zinc-900 dark:text-zinc-100">Authenticate</strong>
                                <span class="mt-1 block text-sm">Click "Authenticate" to verify your credentials</span>
                            </x-ui.step-item>
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="4" color="violet" size="lg">
                                <strong class="text-zinc-900 dark:text-zinc-100">Select Endpoint</strong>
                                <span class="mt-1 block text-sm">Choose an API endpoint from the dropdown menu</span>
                            </x-ui.step-item>
                        </li>
                        <li class="flex items-start">
                            <x-ui.step-item number="5" color="violet" size="lg">
                                <strong class="text-zinc-900 dark:text-zinc-100">Test API Calls</strong>
                                <span class="mt-1 block text-sm">
                                    Configure any necessary parameters as indicated on the page and click "Execute
                                    Request" to send the request and get the data back
                                </span>
                            </x-ui.step-item>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Security Section -->
            <div class="space-y-4">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Security Best Practices</h3>

                <div class="grid gap-4 md:grid-cols-3">
                    <div
                        class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950/20"
                    >
                        <div class="flex items-start space-x-3">
                            <flux:icon.shield-check
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400"
                            />
                            <div>
                                <h4 class="mb-1 font-semibold text-green-800 dark:text-green-200">
                                    Credential Storage
                                </h4>
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    Non-sensitive information is temporarily cached in your browser session
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/20"
                    >
                        <div class="flex items-start space-x-3">
                            <flux:icon.eye-slash
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400"
                            />
                            <div>
                                <h4 class="mb-1 font-semibold text-amber-800 dark:text-amber-200">Sensitive Data</h4>
                                <p class="text-sm text-amber-700 dark:text-amber-300">
                                    Client Secret and Password are never stored and must be re-entered each session
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/20"
                    >
                        <div class="flex items-start space-x-3">
                            <flux:icon.key class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400" />
                            <div>
                                <h4 class="mb-1 font-semibold text-blue-800 dark:text-blue-200">Access Tokens</h4>
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Authentication tokens are stored server-side and cleared on logout
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Troubleshooting Section -->
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Troubleshooting</h3>

                <div class="space-y-6">
                    <div class="rounded-lg border border-red-200 p-6 dark:border-red-800">
                        <h4 class="mb-3 flex items-center font-semibold text-red-800 dark:text-red-200">
                            <flux:icon.x-circle class="mr-2 h-5 w-5" />
                            Authentication Failed
                        </h4>
                        <ul class="space-y-2 text-sm text-red-700 dark:text-red-300">
                            <li class="flex items-start">
                                <span class="mt-2 mr-3 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-red-400"></span>
                                Verify all credentials are entered correctly
                            </li>
                            <li class="flex items-start">
                                <span class="mt-2 mr-3 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-red-400"></span>
                                Check that the hostname includes the protocol (https://)
                            </li>
                            <li class="flex items-start">
                                <span class="mt-2 mr-3 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-red-400"></span>
                                Ensure the API user account is active and has proper permissions
                            </li>
                            <li class="flex items-start">
                                <span class="mt-2 mr-3 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-red-400"></span>
                                Confirm the API client is properly configured in WFM
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-amber-200 p-6 dark:border-amber-800">
                        <h4 class="mb-3 flex items-center font-semibold text-amber-800 dark:text-amber-200">
                            <flux:icon.wifi class="mr-2 h-5 w-5" />
                            Connection Errors
                        </h4>
                        <ul class="space-y-2 text-sm text-amber-700 dark:text-amber-300">
                            <li class="flex items-start">
                                <span class="mt-2 mr-3 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-amber-400"></span>
                                Verify the hostname URL is correct and accessible
                            </li>
                            <li class="flex items-start">
                                <span class="mt-2 mr-3 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-amber-400"></span>
                                Check your network connection
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-blue-200 p-6 dark:border-blue-800">
                        <h4 class="mb-3 flex items-center font-semibold text-blue-800 dark:text-blue-200">
                            <flux:icon.lock-closed class="mr-2 h-5 w-5" />
                            API Permission Errors
                        </h4>
                        <ul class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
                            <li class="flex items-start">
                                <span class="mt-2 mr-3 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-400"></span>
                                Check that your API user has the necessary permissions per the endpoint documentation
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Need Help Section -->
            <div
                class="rounded-lg bg-gradient-to-r from-zinc-50 to-zinc-100 p-6 dark:from-zinc-900 dark:to-zinc-950/50"
            >
                <h3 class="mb-4 flex items-center text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    <flux:icon.question-mark-circle class="mr-2 h-6 w-6 text-blue-600 dark:text-blue-400" />
                    Need Help?
                </h3>

                <flux:text class="mb-4">If you're still having trouble setting up API access:</flux:text>

                <div class="grid gap-4 md:grid-cols-2">
                    <a href="https://developer.ukg.com" target="_blank" class="grid">
                        <div
                            class="cursor-pointer rounded-lg border border-zinc-200 bg-white p-4 transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                        >
                            <flux:icon.book-open
                                class="mb-2 h-8 w-8 text-green-600 transition-colors duration-200 group-hover:text-green-700 dark:text-green-400 dark:group-hover:text-green-300"
                            />
                            <h4 class="mb-1 font-medium text-zinc-900 dark:text-zinc-100">API Documentation</h4>
                            <flux:text variant="subtle">Refer to the Developer documentation</flux:text>
                        </div>
                    </a>

                    <a href="https://community.ukg.com" target="_blank" class="grid">
                        <div
                            class="cursor-pointer rounded-lg border border-zinc-200 bg-white p-4 transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/50"
                        >
                            <flux:icon.computer-desktop
                                class="mb-2 h-8 w-8 text-purple-600 transition-colors duration-200 group-hover:text-purple-700 dark:text-purple-400 dark:group-hover:text-purple-300"
                            />
                            <h4 class="mb-1 font-medium text-zinc-900 dark:text-zinc-100">Support Case</h4>
                            <flux:text variant="subtle">
                                Open a Support Case for assistance with setting up a Client and/or user
                            </flux:text>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
