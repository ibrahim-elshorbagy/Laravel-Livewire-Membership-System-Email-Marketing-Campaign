<div
    class="flex flex-col p-3 border rounded-md md:p-6 group border-neutral-300 bg-neutral-50 text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
    <!-- Tabs -->
    <div x-data="{ selectedTab: @entangle('selectedTab') }" class="w-full">
        <!-- Tab buttons -->
        <div class="flex gap-2 mb-5 overflow-x-auto border-b border-neutral-300 dark:border-neutral-700" role="tablist">
            <button x-on:click="selectedTab = 'users'"
                :class="selectedTab === 'users' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Users</button>
            <button x-on:click="selectedTab = 'admins'"
                :class="selectedTab === 'admins' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Admins</button>
            <button x-on:click="selectedTab = 'verified'"
                :class="selectedTab === 'verified' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Verified Users</button>
            <button x-on:click="selectedTab = 'unverified'"
                :class="selectedTab === 'unverified' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Unverified Users</button>
            <button x-on:click="selectedTab = 'trashed'"
                :class="selectedTab === 'trashed' ? 'font-bold text-black border-b-2 border-black dark:border-orange-500 dark:text-orange-500' : 'text-neutral-600'"
                class="px-4 py-2 text-sm h-min" role="tab">Deleted Users</button>
        </div>

        <!-- Users Tab Content -->
        <div x-show="selectedTab === 'users'">
            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                @include('livewire.pages.admin.user.partials.table', [
                'items' => $this->users,
                'search' => 'search',
                'searchPlaceholder' => 'Search users...',
                'showCreateButton' => true,
                'isTrashed' => false
                ])
            </div>
        </div>

        <!-- Admins Tab Content -->
        <div x-show="selectedTab === 'admins'">
            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                @include('livewire.pages.admin.user.partials.table', [
                'items' => $this->admins,
                'search' => 'adminSearch',
                'searchPlaceholder' => 'Search admins...',
                'showCreateButton' => true,
                'isTrashed' => false
                ])
            </div>
        </div>

        <!-- Verified Users Tab Content -->
        <div x-show="selectedTab === 'verified'">
            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                @include('livewire.pages.admin.user.partials.table', [
                'items' => $this->verifiedUsers,
                'search' => 'verifiedSearch',
                'searchPlaceholder' => 'Search verified users...',
                'showCreateButton' => true,
                'isTrashed' => false
                ])
            </div>
        </div>

    <!-- Verified Users Tab Content -->
        <div x-show="selectedTab === 'unverified'">
            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                @include('livewire.pages.admin.user.partials.table', [
                'items' => $this->unverifiedUsers,
                'search' => 'unverifiedSearch',
                'searchPlaceholder' => 'Search unverifiedSearch users...',
                'showCreateButton' => true,
                'isTrashed' => false
                ])
            </div>
        </div>
        <!-- Trashed Users Tab Content -->
        <div x-show="selectedTab === 'trashed'">
            <div class="w-full overflow-hidden overflow-x-auto rounded-lg">
                @include('livewire.pages.admin.user.partials.table', [
                'items' => $this->trashedUsers,
                'search' => 'trashedSearch',
                'searchPlaceholder' => 'Search deleted users...',
                'showCreateButton' => false,
                'isTrashed' => true
                ])
            </div>
        </div>
    </div>
</div>
