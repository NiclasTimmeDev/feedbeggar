<template>
    <!--  Handling fetching state.-->
    <v-progress-circular
        v-if="$fetchState.pending"
        :size="50"
        color="secondary"
        indeterminate
    ></v-progress-circular>

    <!--  @TODO: Display proper error.-->
    <p v-else-if="$fetchState.error">An error occurred :(</p>

    <div v-else>
      <v-card v-if="status.success">
        <v-card-title>
          Thanks for verifying your email address.
        </v-card-title>
        <v-card-text>You will be redirected in a few seconds</v-card-text>
        <v-card-actions>
          <v-btn
          @click="$router.push('/')"
          color="primary"
          >Go to dashboard now</v-btn>
        </v-card-actions>
      </v-card>

      <v-card v-else-if="status.error">
        <v-card-title>
          Sorry, something went wrong
        </v-card-title>
        <v-card-text>Sorry, something went wrong. Please try again later or contact our support.</v-card-text>
      </v-card>

    </div>
  </template>

  <script>
    export default {

      /*
      *=================================
      * DATA.
      *=================================
    */
      data() {
        return {
          params: {
            userID: "",
            hash: ""
          },
          queries: {
            expires: "",
            signature: ""
          },
          status: {
            error: "",
            success: ""
          }
        }
      },
      /*
      *=================================
      * FETCH HOOK.
      *=================================
      */
      async fetch() {
        // Get params from url
        this.params = {
          userID: this.$route.params.userID,
          hash: this.$route.params.hash
        };

        // Get queries from url.
        this.queries = {
          expires: this.$route.query.expires,
          signature: this.$route.query.signature
        };

        try {
          /*
           * Construct the url the api expects.
           * It must be /email/verify/USERID/HASH?expires=EXPIRES&signature=SIGNATURE
           */
          const requestURL = `/email/verify/${this.params.userID}/${this.params.hash}?expires=${this.queries.expires}&signature=${this.queries.signature}`;

          await this.$axios.get("/sanctum/csrf-cookie");
          const res = await this.$axios.get(requestURL);

          // Success.
          if (res.status === 204) {
            this.status = {
              ...this.status,
              error: false,
              success: true
            }

            // Redirect to homepage after 4 seconds.
            setTimeout(() => {
              this.$router.push('/');
            }, 4000)
          }
        } catch (e) {
          this.status = {
            ...this.status,
            error: "Sorry, somethin went wrong",
            success: false
          }
        }
      },
      /**
       * ==============================
       * Fetch hook
       * ==============================
       */
      head(){
        return {
          title: 'Verify Password'
        }
      }
    }
  </script>