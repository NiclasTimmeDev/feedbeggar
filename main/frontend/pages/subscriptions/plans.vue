<template>
  <div>
    <v-row justify="space-around">
      <v-col cols="12">
        <h1 class="text-h2" >Pricing plans</h1>

      </v-col>

      <v-col v-for="plan in plans" :key="plan.title" cols="12" lg="4" md="6">
        <v-card height="100%">
          <v-card-title class="primary--text">{{ plan.title }}</v-card-title>
          <v-card-subtitle>{{ plan.description }}</v-card-subtitle>
          <v-card-text>
            <h2 class="text-h3">{{ plan.price }}</h2>
            <!-- Feature List-->
            <v-list>
              <v-list-item v-for="feature in plan.features" :key="feature">

                <!-- Feature Icon-->
                <v-list-item-icon>
                  <v-icon v-text="feature.icon"></v-icon>
                </v-list-item-icon>

                <!-- Feature Text-->
                <v-list-item-content>
                  <v-list-item-title v-text="feature.text"></v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </v-card-text>

          <!-- Actions-->
          <v-card-actions class="justify-center">
            <v-btn
                class="w-100"
                color="primary"
                text
                @click="plan.action.method()"
            >
              {{ plan.action.title }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  /**
   * ==============================
   * Head hook
   * ==============================
   */
  head(){
    return {
      title: 'Pricing'
    }
  },
  /**
   * ==============================
   * DATA
   * ==============================
   */
  data() {
    return {
      plans: [
        {
          title: "Starter",
          description: "Perfect if you are just starting out and if your site is not receiving too much traffic",
          price: "Free",
          features: [
            {text: 'Widget integration', icon: "mdi-check"},
            {text: 'One project', icon: "mdi-check"},
            {text: '10 feedback submissions per month', icon: "mdi-check"},
            {text: 'No custom support', icon: "mdi-close"},
          ],
          action: {title: "Stay with this plan", method: this.leave}
        },
        {
          title: "Premium",
          icon: 'mdi-crown',
          description: "Perfect for sites receiving moderate or high traffic that want to capture more data.",
          price: "10€ / month",
          features: [
            {text: 'Widget integration', icon: "mdi-check"},
            {text: 'Unlimited projects', icon: "mdi-check"},
            {text: 'Unlimited feedback submissions', icon: "mdi-check"},
            {text: 'Custom support', icon: "mdi-check"},
          ],
          action: {title: "Upgrade now!", method: this.checkout}
        },
      ]
    }
  },
  /**
   * ==============================
   * METHODS
   * ==============================
   */
  methods: {
    /**
     * Stay with the free plan. Redirect to dashboard.
     */
    leave() {
      return this.$router.push('/');
    },

    /**
     * Upgrade to premium. User will be redirected to stripe checkout.
     */
    async checkout() {
      const res = await this.$axios.post('/api/subscription/intent');

      // You will be redirected to Stripe's secure checkout page
      await this.$stripe.redirectToCheckout({
        sessionId: res.data.session.id,
      });
    }
  },
  /**
   * ==============================
   * METHODS
   * ==============================
   */
  /**
   * Redirect the user to the dashboard if he is already premium.
   *
   * @param ctx
   */
  asyncData({$auth, redirect}) {
    if (parseInt(!$auth.user.is_premium) === 1) {
      redirect('/');
    }
  }
}
</script>