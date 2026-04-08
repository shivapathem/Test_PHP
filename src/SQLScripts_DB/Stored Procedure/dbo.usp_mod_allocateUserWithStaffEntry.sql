USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_allocateUserWithStaffEntry]    Script Date: 09/09/2025 18:46:30 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_mod_allocateUserWithStaffEntry]
	@netlogin VARCHAR(80),
	@currentuserid int,
	@status varchar(10),
	@returnstring  varchar(100),
	@UserEmployeeNumber varchar(100),
	@UserSurname varchar(100),
	@UserFirstName varchar(100),
	@UserEmailAddress varchar(100)
AS
BEGIN

	SET NOCOUNT ON;
		
	DECLARE @newuserid INT
	DECLARE @err int
	DECLARE @err2 int
	DECLARE @rows int
	DECLARE @rows2 int
	DECLARE @returnstring2 VARCHAR(200)
	DECLARE @returnstrin3 VARCHAR(200)
	DECLARE @username VARCHAR(300)
	
	
	SET @returnstring = 'Allocate user has been created successfully.'
	SET @status = 'success'
	SET @returnstring2 = ''
	SET @username = ''
	

	BEGIN TRANSACTION	
			
	IF NOT EXISTS (SELECT 1 
					FROM UserDetails (NOLOCK) 
				   where UD_NetLogin = @netlogin)
	 BEGIN	

		INSERT INTO UserDetails(
					UD_EmpNumber,
					UD_StaffNumber,
					UD_NetLogin,
					UD_DisplayName,
					UD_DisplayFirstName,
					UD_DisplayLastName,
					UD_InternalEmail,
					UD_StartDate,
					UD_Status,					
					UD_CreatedBy,
					UD_CreatedDate)
		  VALUES (
					@UserEmployeeNumber,
					@UserEmployeeNumber + dbo.fn_StaffNumberLastChar(@UserEmployeeNumber),
					@netlogin,
					@UserFirstName+' '+@UserSurname,
					@UserFirstName,
					@UserSurname,
					@UserEmailAddress,
					CAST(GETDATE() AS date),
					1,
					@currentuserid,
					GETDATE()
				 )
		
	  SELECT @err2 = @@ERROR, @rows2 = @@ROWCOUNT

	  IF @rows2 = 0 
		BEGIN
			ROLLBACK TRANSACTION
			SET @returnstring = 'Due to some reason  not able to complete this action';
			SET @status = 'error';
			SELECT @status strstatus , @returnstring strreturnstring
			return 0;
		END
	  
	  IF @err2 <> 0 
		BEGIN
			ROLLBACK TRANSACTION
			SET @returnstring = 'Due to some reason  not able to complete this action'	;
			SET @status = 'error';
			SELECT @status strstatus , @returnstring strreturnstring
			return 0;
		END
	 END

   ELSE
	BEGIN

		SELECT @username = UD_DisplayName
		  FROM UserDetails 
		 where UD_NetLogin = @netlogin 
		 
		SET @returnstring = 'The Network Login you have entered is already a Allocate User.';
		SET @returnstring2 = 'The Name of the user is ';
		SET @status = 'usererror'

	END
	
	 SELECT @status strstatus , 
			@returnstring strreturnstring,
			@returnstring2 strreturnstring2,
			@username strreturnstring3;

	COMMIT TRANSACTION;			
	
END