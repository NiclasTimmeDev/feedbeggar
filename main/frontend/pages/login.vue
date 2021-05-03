<template>
  <v-row justify="center" align="center">
    <v-col cols="12" sm="8" md="6">
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
              @input="$v.email.$touch()"
              @blur="$v.email.$touch()"
            ></v-text-field>

            <!-- PASSWORD -->
            <v-text-field
              v-model="password"
              :error-messages="passwordErrors"
              type="password"
              name="input-10-1"
              label="Password"
              hint="At least 8 characters"
              @input="$v.password.$touch()"
              @blur="$v.password.$touch()"
            ></v-text-field>

            <!-- SUBMIT BUTTON -->
            <v-btn
              class="mr-4"
              color="primary"
              :loading="loading"
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
import { validationMixin } from "vuelidate";
import {
  required,
  maxLength,
  minLength,
  email
} from "vuelidate/lib/validators";
export default {
  // Only guests may access this route.
  auth: "guest",

  mixins: [validationMixin],
  validations: {
    email: { required, email },
    password: { required, minLength: minLength(8) }
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
        await this.$axios.get("/sanctum/csrf-cookie");
        const res = await this.$auth.loginWith("laravelSanctum", {
          data: {
            email: this.email,
            password: this.password
          }
        });
      } catch (error) {
        // Display error badge if something went wrong.
        this.error =
          error.response.data.message || "Sorry, something was wrong.";
      }
      this.loading = false;
    }
  }
};
</script>
