<template>
  <v-chip :color="type === 'Idea' ? ' green' : type === 'Other' ? 'yellow' : 'red'">{{ type }}</v-chip>
</template>

<script>
export default {
  props: ['type'],
}
</script>
