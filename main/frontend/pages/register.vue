<template>
  <v-row justify="center" align="center">
    <v-col cols="12" sm="8" md="6">
      <v-card>
        <!-- ERROR MESSAGE -->
        <v-alert v-if="error" type="error">{{ error }}</v-alert>

        <v-card-title class="headline">
          Register
        </v-card-title>

        <v-card-text>
          <!-- THE FORM -->
          <form @submit.prevent="submit">
            <!-- NAME -->
            <v-text-field
              v-model="name"
              :error-messages="nameErrors"
              label="Name"
              @input="$v.email.$touch()"
              @blur="$v.email.$touch()"
            ></v-text-field>

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

            <!-- CONFIRM PASSWORD -->
            <v-text-field
              v-model="passwordConfirmed"
              :error-messages="passwordConfirmedErrors"
              type="password"
              name="input-10-1"
              label="Repeat Password"
              hint="Must match the password entered above"
              @input="$v.passwordConfirmed.$touch()"
              @blur="$v.passwordConfirmed.$touch()"
            ></v-text-field>

            <!-- SUBMIT BUTTON -->
            <v-btn
              class="mr-4"
              color="primary"
              type="submit"
              :loading="loading"
            >
              Register
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
  email,
  sameAs
} from "vuelidate/lib/validators";

export default {
  // Only guests may access this route.
  auth: "guest",

  mixins: [validationMixin],
  validations: {
    name: { required },
    email: { required, email },
    password: { required, minLength: minLength(8) },
    passwordConfirmed: { sameAs: sameAs("password") }
  },
  data: () => ({
    name: "",
    email: "",
    password: "",
    passwordConfirmed: "",
    loading: false,
    error: ""
  }),
  computed: {
    nameErrors() {
      const errors = [];
      if (!this.$v.name.$dirty) return errors;
      !this.$v.name.required && errors.push("E-mail is required");
      return errors;
    },
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
  methods: {
    async submit() {
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
        const res = await this.$axios.post("/register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmed: this.passwordConfirmed
        });

        if (res.status === 201) {
          this.$router.push("/");
        }
      } catch (error) {
        this.error =
          error.response.data.message || "Sorry, something was wrong.";
      }

      this.loading = false;
    }
  }
};
</script>
