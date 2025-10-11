export default async function (context) {
  const response = await context.$axios.$get('/me')
  context.$auth.setUser(response.user)
}
