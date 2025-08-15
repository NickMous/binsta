<script setup lang="ts">
import {Search, User} from "lucide-vue-next"
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxList
} from "@/components/ui/combobox"
import {SidebarGroup, SidebarMenu, SidebarMenuItem} from "@/components/ui/sidebar";
import {ref, watch} from 'vue'

interface SearchResult {
  url: string;
  type: 'user';
  title: string;
  subtext?: string;
}

const searchInput = ref('')
const searchResults = ref<SearchResult[]>([])
let debounceTimeout: ReturnType<typeof setTimeout> | null = null

// Watch for changes in search input and call API with debounce
watch(searchInput, (newValue) => {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout)
  }

  debounceTimeout = setTimeout(async () => {
    if (newValue.trim()) {
      try {
        const response = await fetch(`/api/search/${encodeURIComponent(newValue)}`)
        if (response.ok) {
          searchResults.value = (await response.json()).results;
        }
      } catch (error) {
        console.error('Search API error:', error)
      }
    } else {
      searchResults.value = []
    }
  }, 300) // 300ms debounce delay
})

</script>

<template>
  <SidebarGroup>
    <SidebarMenu>
      <SidebarMenuItem>
        <Combobox by="label">
          <ComboboxAnchor>
            <div class="relative w-full max-w-sm items-center">
              <ComboboxInput
                  v-model="searchInput"
                  :display-value="(val) => val?.label ?? ''"
                  placeholder="Search for..."
              />
              <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                <Search class="size-4 text-muted-foreground"/>
              </span>
            </div>
          </ComboboxAnchor>

          <ComboboxList>
            <ComboboxEmpty>
              Nothing found...
            </ComboboxEmpty>

            <ComboboxGroup>
              <RouterLink
                  v-for="result in searchResults"
                  :key="result.url"
                  :to="result.url"
              >
                <ComboboxItem
                    :value="result"
                >
                  <User />
                  {{ result.title || result.url }}
                  <span v-if="result.subtext" class="text-muted-foreground text-xs">
                    {{ result.subtext }}
                  </span>
                </ComboboxItem>
              </RouterLink>
            </ComboboxGroup>
          </ComboboxList>
        </Combobox>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>

<style scoped>

</style>