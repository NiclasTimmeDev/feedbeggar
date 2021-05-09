<template>
  <v-row align="center" justify="center">
    <v-col cols="12" md="6" sm="8">
      <v-card>
        <!-- Error message -->
        <v-alert v-if="error" type="error">{{ error }}</v-alert>

        <v-card-title class="headline">
          Change your password
        </v-card-title>

        <v-card-subtitle>
          You can now change your password.
        </v-card-subtitle>
        <v-card-text>
          <!-- THE FORM -->
          <form @submit.prevent="submit">

            <!-- EMAIL -->
            <v-text-field
                v-model="email"
                :error-messages="emailErrors"
                label="Email"
                hint="The email address for your account"
                @blur="$v.email.$touch()"
                @input="$v.email.$touch()"
            ></v-text-field>

            <!-- PASSWORD -->
            <v-text-field
                v-model="password"
                :error-messages="passwordErrors"
                label="Password"
                type="password"
                hint="Your new password"
                @blur="$v.password.$touch()"
                @input="$v.password.$touch()"
            ></v-text-field>

            <!-- PASSWORD CONFIRMED-->
            <v-text-field
                v-model="passwordConfirmed"
                :error-messages="passwordConfirmedErrors"
                label="Confirm Password"
                type="password"
                hint="Confirm your new password"
                @blur="$v.passwordConfirmed.$touch()"
                @input="$v.passwordConfirmed.$touch()"
            ></v-text-field>

            <!-- SUBMIT BUTTON -->
            <v-btn
                :loading="loading"
                class="mr-4"
                color="primary"
                type="submit"
            >
              Change password
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

import {validationMixin} from "vuelidate";
import {email, minLength, required, sameAs} from "vuelidate/lib/validators";

export default {
  auth: 'guest',
  layout: 'guest',

  /*
  *=================================
  * VALIDATION CONFIG.
  *=================================
  */
  mixins: [validationMixin],
  validations: {
    email: {required, email},
    password: {required, minLength: minLength(8)},
    passwordConfirmed: {sameAs: sameAs("password")}
  },
  /*
  *=================================
  * DATA.
  *=================================
  */
  data(){
    return {
      loading: false,
      email: "",
      password: "",
      passwordConfirmed: "",
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
* METHODS.
*=================================
*/
  methods: {
    async submit(){
      // Validate and exit if we have errors.
      this.$v.$touch();

      // Unset error.
      this.error = "";

      if (this.$v.$invalid) {
        return;
      }

      // Show the loading button.
      this.loading = true;

      try {
        await this.$axios.get("/sanctum/csrf-cookie");
        const res = await this.$axios.post('/password/reset', {
          email: this.email,
          password: this.password,
          password_confirmation: this.passwordConfirmed,
          token: this.$route.params.token
        });


        if(res.status === 200) {
          this.snackbar = {
            ...this.snackbar,
            show: true,
            error: false,
            text: "Password changed successfully."
          }

          setTimeout(()=>{this.$router.push('/login')}, 3000)
        }

      } catch(e) {
        this.snackbar = {
          ...this.snackbar,
          show: true,
          error: true,
          text: "Sorry, something went wrong. Please try again later."
        }
      }

      this.loading = false;
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
    },
    passwordErrors() {
      const errors = [];
      if (!this.$v.password.$dirty) return errors;
      !this.$v.password.required && errors.push("Password is required");
      !this.$v.password.minLength &&
      errors.push("Password must be at least 8 characters long.");
      return errors;
    },
    passwordConfirmedErrors() {
      const errors = [];
      if (!this.$v.passwordConfirmed.$dirty) return errors;
      !this.$v.passwordConfirmed.sameAs &&
      errors.push("Passwords must be equal");
      return errors;
    }
  },



}
</script>