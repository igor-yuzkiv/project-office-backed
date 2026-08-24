<script setup lang="ts">
import { computed, useId } from 'vue'
import { Icon } from '@iconify/vue'
import { RouterLink } from 'vue-router'
import { formatDate } from '@/shared/utils/date.util'
import { UserAvatar } from '@/widgets/user/user-avatar'
import {
    ACTIVITY_ACCENT_CLASSES,
    UNKNOWN_ACTIVITY_TYPE,
    resolveActivityType,
    resolveSubjectRouteName,
    type AuditRecordDto,
} from '@/entities/audit-trail'

const props = defineProps<{ record: AuditRecordDto; expanded: boolean }>()

defineEmits<{ (e: 'toggle'): void }>()

const detailsId = useId()

const typeDef = computed(() => resolveActivityType(props.record.type))
const isUnknownType = computed(() => typeDef.value === UNKNOWN_ACTIVITY_TYPE)

// The shared helper swallows an unparseable timestamp instead of throwing mid-render: one bad
// row must not take the whole feed down with it.
const time = computed(() => formatDate(props.record.created_at, 'HH:mm') ?? '')
const exactTime = computed(() => formatDate(props.record.created_at, 'MMM d, HH:mm') ?? 'Unknown time')

/** Anonymous records are common: the author may be gone, or the event may come from the console. */
const actorName = computed(() => props.record.actor?.name ?? 'Someone')

const subjectRoute = computed(() => {
    const subject = props.record.subject

    if (!typeDef.value.linkable || subject === null) {
        return null
    }

    const name = resolveSubjectRouteName(subject.type)

    return name === null ? null : { name, params: { id: subject.id } }
})

/** Four different reasons a row leads nowhere, and each of them is a different sentence. */
const noLinkReason = computed(() => {
    if (subjectRoute.value !== null) {
        return null
    }

    if (isUnknownType.value) {
        return 'This type has no renderer yet — shown as stored, without a link.'
    }

    if (props.record.subject === null) {
        return 'This event is not about a single entity — nothing to open.'
    }

    if (!typeDef.value.linkable) {
        return 'This entity no longer exists — nothing to open.'
    }

    return 'This kind of entity has no page yet — nothing to open.'
})

/** Excerpts of user-written text read better quoted; generated one-liners do not. */
const isQuotedDescription = computed(() =>
    ['comment.created', 'task.checkpoint', 'task.handoff'].includes(props.record.type)
)
</script>

<template>
    <div class="border-surface-100 dark:border-surface-800 border-t first:border-t-0">
        <button
            type="button"
            class="hover:bg-surface-50 dark:hover:bg-surface-800/60 focus-visible:outline-primary gap-2.5 px-3.5 py-2.5 grid w-full cursor-pointer grid-cols-[2.125rem_1.375rem_1fr_auto_1.125rem] items-center text-left focus-visible:outline-2 focus-visible:-outline-offset-2"
            :aria-expanded="expanded"
            :aria-controls="detailsId"
            @click="$emit('toggle')"
        >
            <span
                class="grid size-[2.125rem] place-items-center rounded-full"
                :class="ACTIVITY_ACCENT_CLASSES[typeDef.accent]"
                aria-hidden="true"
            >
                <Icon :icon="typeDef.icon" class="size-4" />
            </span>

            <!-- The author is already named in the title; the initials would only be read twice. -->
            <span aria-hidden="true" class="contents">
                <UserAvatar
                    :initials="record.actor?.initials ?? '?'"
                    :avatar-url="record.actor?.avatar_url"
                    size="small"
                />
            </span>

            <span class="text-surface-800 dark:text-surface-100 min-w-0 text-sm truncate">
                {{ record.title }}
            </span>

            <span class="text-surface-400 text-xs whitespace-nowrap tabular-nums">{{ time }}</span>

            <Icon
                icon="heroicons:chevron-right"
                class="text-surface-400 size-3.5 transition-transform motion-reduce:transition-none"
                :class="{ 'rotate-90': expanded }"
                aria-hidden="true"
            />
        </button>

        <div
            v-show="expanded"
            :id="detailsId"
            class="bg-surface-50 dark:bg-surface-900 border-surface-100 dark:border-surface-800 gap-3 px-3.5 pt-0.5 pb-4 flex flex-col border-t border-dashed pl-[4.25rem]"
        >
            <p
                v-if="record.description"
                class="text-surface-600 dark:text-surface-300 text-sm max-w-[60ch] whitespace-pre-wrap"
                :class="{ 'border-surface-200 dark:border-surface-700 pl-2.5 border-l-2': isQuotedDescription }"
            >
                {{ record.description }}
            </p>

            <p class="text-surface-400 gap-x-5 gap-y-1.5 text-xs flex flex-wrap">
                <span
                    ><span class="text-surface-500 dark:text-surface-300 font-semibold">Type:</span>
                    <code>{{ record.type }}</code></span
                >
                <span
                    ><span class="text-surface-500 dark:text-surface-300 font-semibold">When:</span>
                    {{ exactTime }}</span
                >
                <span
                    ><span class="text-surface-500 dark:text-surface-300 font-semibold">By:</span> {{ actorName }}</span
                >
            </p>

            <RouterLink
                v-if="subjectRoute"
                :to="subjectRoute"
                class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-800 hover:border-primary text-primary gap-1.5 rounded-lg px-2.5 py-1 text-sm font-semibold inline-flex items-center self-start border"
            >
                Open
                <Icon icon="heroicons:arrow-up-right" class="size-3" />
            </RouterLink>

            <p v-else class="text-surface-400 text-xs italic">{{ noLinkReason }}</p>
        </div>
    </div>
</template>
