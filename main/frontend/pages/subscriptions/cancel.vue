<template>
  <v-row justify="center">
    <v-col cols="6">
      <v-card>
        <!--Title-->
        <v-card-title>
          <h1 class="text-h2">
            {{ title }}
          </h1>
        </v-card-title>

        <!--Text-->
        <v-card-text>
          <p>{{ text1 }}</p>
        </v-card-text>

        <!--Action-->
        <v-card-actions>
              <v-dialog
                  v-model="dialog.show"
                  width="500"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                      text
                      v-bind="attrs"
                      v-on="on"
                  >
                    Cancel
                  </v-btn>
                </template>

                <v-card>
                  <v-card-title class="headline">
                    {{dialog.title}}
                  </v-card-title>

                  <v-card-text>
                   {{dialog.text}}
                  </v-card-text>

                  <v-card-actions>
                    <v-btn
                        color="error"
                        text
                        @click="cancel"
                        :loading="cancellation.loading"
                    >
                      Cancel
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
        </v-card-actions>
      </v-card>
    </v-col>

    <!--==============================-->
    <!-- Snackbar for new projects.-->
    <!--==============================-->
    <v-snackbar
        v-model="cancellation.snackbar.show"
        :timeout="cancellation.snackbar.timeout"
    >
      <span :class="cancellation.snackbar.error ? 'error--text' : 'green--text'">
       {{ cancellation.snackbar.text }}
      </span>
      <template v-slot:action="{ attrs }">
        <v-btn
            v-bind="attrs"
            color="white"
            text
            @click="cancellation.snackbar.show = false"
        >
          Got it
        </v-btn>
      </template>
    </v-snackbar>
  </v-row>
</template>

<script>
export default {
  data() {
    return {
      title: 'Are you sure?',
      text1: 'If you cancel your subscription, you can from next month on only use one project and receive only 10 feedback submissions.',
      dialog: {
        show: false,
        title: 'Confirm cancellation',
        text: 'By clicking on "Cancel" your subscription will be cancelled next month. Please make sure do DELETE ALL PROJECTS EXCEPT ONE. Otherwise, you cannot cancel your subscription.'
      },
      cancellation: {
        loading: false,
        // Snackbar that shows error or success when creating new bucket.
        snackbar: {
          timeout: 6000,
          show: false,
          error: false,
          text: ""
        },
      }
    }
  },
  methods: {
    async cancel(){
      this.cancellation.loading = true;
      try {
        const res = await this.$axios.post('/api/subscription/cancel');

        if(res.status === 200) {
          this.cancellation.snackbar = {
            ...this.cancellation.snackbar,
            show: true,
            error: false,
            text: "Premium subscription cancelled successfully. You will be redirected shortly."
          }

          setTimeout(()=>{
            this.$router.push('/');
          }, 3000)
        }
      } catch(e) {
        const res = e.response;

        this.cancellation.snackbar = {
          ...this.cancellation.snackbar,
          show: true,
          error: true,
          text: res.status === 400 ? res.data.error : "Sorry, something went wrong."
        }
      }
      this.cancellation.loading = false;
      this.dialog.show = false;

    }
  },
  asyncData({$auth, redirect}) {
    // User can only access this page if he/she is a premium user.
    if (parseInt($auth.user.is_premium) === 0) {
      redirect('/');
    }
  }
}
</script>