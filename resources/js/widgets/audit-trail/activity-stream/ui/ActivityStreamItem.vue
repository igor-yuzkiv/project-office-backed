<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { formatDate, formatDateTime } from '@/shared/utils/date.util'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { resolveActivityType, resolveSubjectRouteName, type AuditRecordDto } from '@/entities/audit-trail'
import { splitTitleByKey } from '../lib'

const props = defineProps<{ record: AuditRecordDto; expanded: boolean; showProject: boolean }>()

defineEmits<{ (e: 'toggle'): void }>()

// The shared helper swallows an unparseable timestamp instead of throwing mid-render: one bad
// row must not take the whole feed down with it.
const time = computed(() => formatDate(props.record.created_at, 'HH:mm') ?? '')
const exactTime = computed(() => formatDateTime(props.record.created_at) ?? undefined)

const subjectRoute = computed(() => {
    const subject = props.record.subject

    if (!resolveActivityType(props.record.type).linkable || subject === null) {
        return null
    }

    const name = resolveSubjectRouteName(subject.type)

    return name === null ? null : { name, params: { id: subject.id } }
})

// A creation event describes itself with the subject's name, which the row already shows.
const description = computed(() => {
    const text = props.record.description
    return text && text !== props.record.subject?.name ? text : null
})

const titleSegments = computed(() => splitTitleByKey(props.record.title, props.record.subject?.key ?? null))

/** The subject's name is already the title's second half in some events; then it is not repeated. */
const subjectName = computed(() => {
    const name = props.record.subject?.name

    return name && !props.record.title.includes(name) ? name : null
})
</script>

<template>
    <div class="hairline gap-x-3 py-2.5 grid grid-cols-[auto_1fr_auto] items-start last:border-b-0">
        <!-- The author is already named in the title; the initials would only be read twice. -->
        <span aria-hidden="true" class="contents">
            <UserAvatar
                :initials="record.actor?.initials ?? '?'"
                :avatar-url="record.actor?.avatar_url"
                size="xsmall"
                class="mt-px"
            />
        </span>

        <span class="min-w-0">
            <span class="text-ink gap-x-1.5 min-w-0 text-sm flex flex-wrap items-baseline">
                <span class="min-w-0">
                    <template v-for="(segment, index) in titleSegments" :key="index">
                        <RouterLink
                            v-if="segment.isKey && subjectRoute"
                            :to="subjectRoute"
                            class="text-accent font-medium hover:underline"
                        >
                            {{ segment.text }}
                        </RouterLink>
                        <template v-else>{{ segment.text }}</template>
                    </template>
                </span>
                <span v-if="subjectName" class="type-meta min-w-0 truncate">{{ subjectName }}</span>
                <span v-if="showProject && record.project" class="type-meta-3 whitespace-nowrap"
                    >· {{ record.project.name }}</span
                >
            </span>

            <button
                v-if="description"
                type="button"
                class="type-meta mt-0.5 block w-full cursor-pointer text-left"
                :class="expanded ? 'whitespace-pre-wrap' : 'line-clamp-1'"
                :aria-expanded="expanded"
                @click="$emit('toggle')"
            >
                {{ description }}
            </button>
        </span>

        <time class="type-meta-3 whitespace-nowrap tabular-nums" :datetime="record.created_at" :title="exactTime">
            {{ time }}
        </time>
    </div>
</template>
