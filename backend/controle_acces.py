if user_role == "Admin":
    # Accès à toutes les fonctionnalités
    show_admin_dashboard()

elif user_role == "Organisateur":
    # Accès à certaines fonctionnalités
    show_event_management_page()

elif user_role == "Utilisateur":
    # Accès limité aux actions d'utilisateur
    show_event_list()

else:
    # Accès très limité pour les visiteurs
    show_event_list_view_only()
