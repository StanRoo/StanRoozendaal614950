export function handleApiError(error) {
    if (error.response) {
      const status = error.response.status;
      const message = error.response.data?.error || "An error occurred.";
  
      const errorMessages = {
        400: message,
        401: "Invalid username or password.",
        403: "You don't have permission to access this resource.",
        404: "Resource not found.",
        500: "Server error. Please try again later."
      };
  
      return errorMessages[status] || message;
    } else if (error.request) {
      return "Network error. Please check your internet connection.";
    } else {
      return "An unexpected error occurred.";
    }
  }