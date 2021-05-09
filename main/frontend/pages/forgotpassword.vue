<template>
  <v-row align="center" justify="center">
    <v-col cols="12" md="6" sm="8">
      <v-card>
        <!-- Error message -->
        <v-alert v-if="error" type="error">{{ error }}</v-alert>

        <v-card-title class="headline">
          Forgot you password?
        </v-card-title>

        <v-card-subtitle>
          Don't worry, we've got your back. Just enter your email address and we'll send you a magic link where you can
          reset your password.
        </v-card-subtitle>
        <v-card-text>
          <!-- THE FORM -->
          <form @submit.prevent="submit">
            <!-- EMAIL -->
            <v-text-field
                v-model="email"
                :error-messages="emailErrors"
                label="E-mail"
                @blur="$v.email.$touch()"
                @input="$v.email.$touch()"
            ></v-text-field>

            <!-- SUBMIT BUTTON -->
            <v-btn
                :loading="loading"
                class="mr-4"
                color="primary"
                type="submit"
            >
              Reset password
            </v-btn>
          </form>
        </v-card-text>
      </v-card>
    </v-col>

    <!--SNACKBAR-->
    <v-snackbar
        v-model="snackbar.show"
        :timeout="snackbar.timeout"
    >
      <span :class="snackbar.error ? 'error--text' : 'green--text'">
       {{ snackbar.text }}
      </span>
      <template v-slot:action="{ attrs }">
        <v-btn
            v-bind="attrs"
            color="white"
            text
            @click="snackbar.show = false"
        >
          Got it
        </v-btn>
      </template>
    </v-snackbar>
  </v-row>
</template>

<script>
import {email, required} from "vuelidate/lib/validators";
import {validationMixin} from "vuelidate";

export default {
  // Only guests may access this route.
  auth: "guest",

  // Unauthenticated layout.
  layout: 'guest',

  mixins: [validationMixin],
  validations: {
    email: {required, email},
  },
  /*
 *=================================
 * DATA.
 *=================================
 */
  data() {
    return {
      loading: false,
      email: "",
      error: "",
      snackbar: {
        timeout: 6000,
        show: false,
        error: false,
        text: ""
      },
    }
  },
  /*
  *=================================
  * COMPUTED.
  *=================================
  */
  computed: {
    emailErrors() {
      const errors = [];
      if (!this.$v.email.$dirty) return errors;
      !this.$v.email.email && errors.push("Must be valid e-mail");
      !this.$v.email.required && errors.push("E-mail is required");
      return errors;
    }
  },
  /*
  *=================================
  * METHODS.
  *=================================
  *
  */
  methods: {
    async submit() {
      this.$v.$touch();

      if (this.$v.$invalid) {
        return;
      }

      this.loading = true;

      try {
        const res = await this.$axios.post('/password/email', {
          email: this.email
        });

        if (res.status === 200) {
          this.snackbar = {
            ...this.snackbar,
            show: true,
            error: false,
            text: "Alright. We sent you a link to reset your password via mail."
          }
        }
      } catch (e) {
        this.snackbar = {
          ...this.snackbar,
          show: true,
          error: true,
          text: "Sorry, something went wrong. Please try again later."
        }
      }

      this.loading = false;

    }
  }
}
</script>