<template>
  <v-footer
      padless
  >
    <v-row
        class="align-center"
        justify="center"
        no-gutters
    >
      <nuxt-link
          v-for="link in links"
          :key="link.title"
          :to="link.link"

      >
        <v-btn
            class="my-2"
            color="primary"
            rounded
            text
        >
          {{ link.title }}
        </v-btn>

      </nuxt-link>
    </v-row>
  </v-footer>
</template>

<script>
export default {
  data: () => ({
    links: [
      {
        title: "Home",
        link: process.env.MARKETING_HOME_URL,
      },
      {
        title: "Login",
        link: "/login",
      },
      {
        title: "Register",
        link: "/register",
      },
      {
        title: "Imprint",
        link: "/imprint",
      },
      {
        title: "Privacy policy",
        link: "/privacy",
      },
      {
        title: "Terms & conditions",
        link: "/terms",
      },
    ],
  }),
}
</script>