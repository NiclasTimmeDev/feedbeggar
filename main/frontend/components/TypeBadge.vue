<template>
  <v-chip :color="type === 'idea' ? ' green' : type === 'other' ? 'yellow' : 'red'">{{ type }}</v-chip>
</template>

<script>
export default {
  //color: 'green',
  //color: type === 'idea' ? ' green' : type === 'other' ? 'yellow' : 'red',
  props: ['type'],
}
</script>
