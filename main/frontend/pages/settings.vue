<template>
  <div>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h2 mb-5">Settings</h1>
      </v-col>
    </v-row>

    <!-- Accordions -->
    <v-expansion-panels>

      <!-- Profile-->
      <v-expansion-panel>
        <v-expansion-panel-header>
          <h2>
            Profile
          </h2>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <form @submit.prevent="updateProfile"
          >
            <v-row>

              <v-col cols="6">
                <v-text-field
                    v-model="profile.name"
                    :error-messages="nameErrors"
                    label="Name*"
                    required
                    @blur="$v.profile.name.$touch()"
                ></v-text-field>
              </v-col>

            </v-row>
            <v-row>
              <v-col cols="12">
                <v-btn
                    :loading="profile.meta.loading"
                    color="secondary"
                    text
                    type="submit"
                >
                  Save
                </v-btn>
              </v-col>
            </v-row>
          </form>
        </v-expansion-panel-content>
      </v-expansion-panel>

      <!-- Subscription-->
      <v-expansion-panel>
        <v-expansion-panel-header>
          <h2>
            Subscription
          </h2>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <!-- If user is premium-->
          <div v-if="parseInt($auth.user.is_premium) === 1">
            <div>Awesome, you have a premium subscription! This means you can create unlimited projects and receive
              unlimited feedback
            </div>

            <nuxt-link class="text-decoration-none" to="/subscriptions/cancel" >
              <v-btn
                  text
                  class="mt-5 text-decoration-none"
              >Cancel subscription
              </v-btn>
            </nuxt-link>

          </div>

          <!-- If user is Not premium-->
          <div v-else>
            <div>You are currently on the free plan. Consider upgrading to premium to create unlimited projects and
              receive unlimited feedback
            </div>

            <nuxt-link class="text-decoration-none" to="/subscriptions/plans" >
              <v-btn
                  color="primary"
                  class="mt-5 text-decoration-none"
              >Upgrade
              </v-btn>
            </nuxt-link>
          </div>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>

    <v-row class="mt-5">
      <v-col cols="12">
        <v-btn
            :loading="logout.loading"
            class="ma-2 white--text"
            color="error"
            @click="submitLogout"
        >
          Logout
          <v-icon
              right
          >
            mdi-logout
          </v-icon>
        </v-btn>
      </v-col>
    </v-row>

    <v-snackbar
        v-model="profile.snackbar.show"
        :timeout="profile.snackbar.timeout"
    >
      <span :class="profile.snackbar.error ? 'error--text' : 'green--text'">
       {{ profile.snackbar.text }}
      </span>
      <template v-slot:action="{ attrs }">
        <v-btn
            v-bind="attrs"
            color="white"
            text
            @click="profile.snackbar.show = false"
        >
          Got it
        </v-btn>
      </template>
    </v-snackbar>

  </div>
</template>

<script>
import {validationMixin} from "vuelidate";
import {required} from "vuelidate/lib/validators";

export default {
  data() {
    return {
      profile: {
        name: this.$auth.user.name,
        meta: {
          loading: false,
          error: ""
        },
        snackbar: {
          timeout: 6000,
          show: false,
          error: false,
          text: ""
        },
      },
      logout: {
        loading: false
      }
    }
  },
  /**
   * ==============================
   * Validation for new bucket.
   * ==============================
   */
  mixins: [validationMixin],
  validations: {
    profile: {
      name: {required},
    }
  },
  /**
   * ==============================
   * METHODS.
   * ==============================
   */
  methods: {
    async updateProfile() {
      // Validate the input data.
      this.$v.$touch();
      if (this.$v.$invalid) {
        return;
      }

      this.profile.meta.loading = true;

      try {
        const res = await this.$axios.patch(`/api/profile`, {
          updates: {name: this.profile.name}
        });

        // Success.
        if (res.status === 200) {
          this.profile.meta = {
            ...this.profile.meta,
            loading: false,
            error: ""
          };
          this.profile.snackbar = {
            ...this.profile.snackbar,
            show: true,
            error: false,
            text: "Profile edited successfully"
          }
        }
      } catch (e) {
        this.profile.meta = {
          ...this.profile.meta,
          loading: false,
          error: "Something went wrong."
        };
        this.profile.snackbar = {
          ...this.profile.snackbar,
          show: true,
          error: true,
          text: "Something went wrong. Please try again later."
        }
      }
    },
    /**
     * Log the user out.
     */
    async submitLogout() {
      try {
        const res = await this.$axios.post("/logout");
        if (res.status === 204) {
          window.location.reload();
        }
      } catch (error) {
        console.log(error);
      }
    }
  },
  /**
   * ==============================
   * Computed.
   * ==============================
   */
  computed: {
    nameErrors() {
      const errors = [];
      if (!this.$v.profile.name.$dirty) return errors;
      !this.$v.profile.name.required && errors.push("Name is required");
      return errors;
    },
  },
  /**
   * ==============================
   * Head hook
   * ==============================
   */
  head() {
    return {
      title: 'Settings'
    }
  }
}
</script>