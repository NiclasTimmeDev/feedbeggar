<template>
  <v-row align="center" justify="center">
    <v-col cols="12" md="6" sm="8">
      <v-card>
        <!-- Error message -->
        <v-alert v-if="error" type="error">{{ error }}</v-alert>

        <v-card-title class="headline">
          Login
        </v-card-title>
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

            <!-- PASSWORD -->
            <v-text-field
                v-model="password"
                :error-messages="passwordErrors"
                hint="At least 8 characters"
                label="Password"
                name="input-10-1"
                type="password"
                @blur="$v.password.$touch()"
                @input="$v.password.$touch()"
            ></v-text-field>

            <!-- Register and forgot password links.-->
            <div class="d-flex justify-space-between mb-5">
              <div class="">Don't have an account?
                <nuxt-link to="/register">Register</nuxt-link>
              </div>
              <nuxt-link to="/forgotpassword">Forgot password?</nuxt-link>
            </div>


            <!-- SUBMIT BUTTON -->
            <v-btn
                :loading="loading"
                class="mr-4"
                color="primary"
                type="submit"
            >
              Login
            </v-btn>
          </form>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>

<script>
import {validationMixin} from "vuelidate";
import {email, minLength, required} from "vuelidate/lib/validators";

export default {
  // Only guests may access this route.
  auth: "guest",

  // Unauthenticated layout.
  layout: 'guest',

  mixins: [validationMixin],
  validations: {
    email: {required, email},
    password: {required, minLength: minLength(8)}
  },
  data: () => ({
    email: "",
    password: "",
    loading: false,
    error: false
  }),
  computed: {
    passwordErrors() {
      const errors = [];
      if (!this.$v.password.$dirty) return errors;
      !this.$v.password.required && errors.push("Password is required");
      !this.$v.password.minLength &&
      errors.push("Password must be at least 8 characters long.");
      return errors;
    },
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
   * Methods.
   *=================================
   */
  methods: {
    /*
     * The submit function.
     */
    async submit() {
      this.$v.$touch();

      this.error = "";

      if (this.$v.$invalid) {
        return;
      }

      this.loading = true;

      // Try login.
      try {
        const res = await this.$auth.loginWith("laravelSanctum", {
          data: {
            email: this.email,
            password: this.password
          }
        });
      } catch (error) {
        const resCode = error.response.status;

        // Wrong credentials.
        if(resCode === 422) {
          this.error = "The given credentials are invalid."
        }

        // Server error.
        else {
          this.error = "Sorry, something went wrong."
        }
      }
      this.loading = false;
    }
  },
  /**
   * ==============================
   * Head hook
   * ==============================
   */
  head(){
    return {
      title: 'Login'
    }
  }
};
</script>
