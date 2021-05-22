<template>
  <v-row align="center" justify="center">
    <v-col cols="12" md="6" sm="8">
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
                @blur="$v.email.$touch()"
                @input="$v.email.$touch()"
            ></v-text-field>

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

            <!-- CONFIRM PASSWORD -->
            <v-text-field
                v-model="passwordConfirmed"
                :error-messages="passwordConfirmedErrors"
                hint="Must match the password entered above"
                label="Repeat Password"
                name="input-10-1"
                type="password"
                @blur="$v.passwordConfirmed.$touch()"
                @input="$v.passwordConfirmed.$touch()"
            ></v-text-field>

            <p class="text-caption">
              By clicking on "register" you accept our
              <nuxt-link to="terms">Terms & conditions</nuxt-link>
              and our
              <nuxt-link to="privacy">privacy policy</nuxt-link>
              .
            </p>


            <div class="mb-5">Already have an account?
              <nuxt-link to="/login">Login</nuxt-link>
            </div>

            <!-- SUBMIT BUTTON -->
            <v-btn
                :loading="loading"
                class="mr-4"
                color="primary"
                type="submit"
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
import {validationMixin} from "vuelidate";
import {email, minLength, required, sameAs} from "vuelidate/lib/validators";

export default {
  // Only guests may access this route.
  auth: "guest",

  layout: 'guest',

  mixins: [validationMixin],
  validations: {
    name: {required},
    email: {required, email},
    password: {required, minLength: minLength(8)},
    passwordConfirmed: {sameAs: sameAs("password")}
  },
  /**
   * ==============================
   * DATA
   * ==============================
   */
  data: () => ({
    name: "",
    email: "",
    password: "",
    passwordConfirmed: "",
    loading: false,
    error: ""
  }),
  /**
   * ==============================
   * COMPUTED
   * ==============================
   */
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
  /**
   * ==============================
   * METHODS
   * ==============================
   */
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

        this.loading = false;
        if (res.status === 201) {
          window.location.reload();
        }
      } catch (error) {
        this.loading = false;
        const res = error.response;

        if (res.status === 500) {
          return this.error = "Sorry, something went wrong. Please try again later."
        }

        if(res.status === 422) {
          const errors = res.data.errors;

          if(errors.email) {
            return this.error = errors.email[0];
          }

          if(errors.password) {
            return this.error = errors.password[0];
          }
        }
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
      title: 'Register'
    }
  }
};
</script>
