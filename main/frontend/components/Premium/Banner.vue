<!--
A banner component that is only show to users who are not premium subscribers.
It asks the users to subscripe to the premium plan.
The banner is included in the default layout.
-->

<template>
  <v-banner
      v-if="isPremium"
      v-model="show"
      elevation="5"
      icon="mdi-crown"
      outlined="outlined"
      single-line
  >Want to receive more feedback?
    <!--Link to upgrade.-->
    <nuxt-link to="/subscriptions/plans">Upgrade to premium now!</nuxt-link>

    <!--Hide the banner.-->
    <template v-slot:actions>
      <v-btn
          color="secondary"
          text
          @click="notNow"
      >
        Not now
      </v-btn>
    </template>

  </v-banner>
</template>

<script>
export default {
  data() {
    return {
      isPremium: parseInt(this.$auth.user.is_premium),
      show: this.$cookies.get('upgrade') === undefined || this.$cookies.get('upgrade') !== 1
    }
  },
  /**
   * ==============================
   * METHODS
   * ==============================
   */
  methods: {
    /**
     * Hide the banner. Also, set a cookie
     * that expires in 24h. Until then. the banner will not be shown
     * anymore.
     */
    notNow(){

      this.show = false;
      this.$cookies.set("upgrade", 1, {
        path: '/',
        maxAge: 60 * 60 * 24
      });

    }
  }
}
</script>