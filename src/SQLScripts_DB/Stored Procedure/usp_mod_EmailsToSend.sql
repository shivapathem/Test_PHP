USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_EmailsToSend]    Script Date: 05/09/2024 18:56:22 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR  ALTER  PROCEDURE [dbo].[usp_mod_EmailsToSend] 
	-- Add the parameters for the stored procedure here
	@netlogin varchar(100) = NULL,
	@currentuser  INT = 1
	--exec usp_mod_EmailsToSend 'KushwVW1',6
AS
BEGIN

/*DECLARE @showResult VARCHAR(50)
DECLARE @TempRequest    TABLE (strstatus INT)
DECLARE @return_value int

INSERT INTO @TempRequest
exec usp_get_UserRolePermissions_ByFormId 0,0,0,'Leave',@netlogin
select @return_value = strstatus from @TempRequest
IF (@return_value is null)
BEGIN	
SET @showResult = 'Access Denied'	
SELECT @showResult returnstatus;
return;
END*/
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	declare @Status int, @StrStatus varchar(500), @err int, @rows int;
	SET @Status =3 
	SET @StrStatus ='email sent'
	
BEGIN TRANSACTION
   UPDATE       LeaveApplications
          SET          Sent = 1,LastModDate=getutcdate(),LastModBy=@currentuser
                       FROM LeaveApplications LA
          INNER JOIN   leave_types ON LA.LeaveTypesID = leave_types.id
          INNER JOIN   LeaveRequestGroups ON leave_types.GroupID = LeaveRequestGroups.id
		  WHERE  ( LA.Sent = 0 )
			   AND ( LA.Deleted = 0 )
			   AND ( LA.Approved = 1 )
			   AND ( LA.Login = CASE WHEN @netlogin Is Null
			   THEN LA.Login else @netlogin end)
			   AND LA.sentOptionValue IN (0, 2)
			   AND ( LA.sentOptionValue = CASE WHEN @netlogin Is Null 
			   then 0 
			   else la.sentOptionValue end)

		  SELECT @err = @@ERROR, @rows = @@ROWCOUNT

		 
		  
		IF @err <> 0
		BEGIN
			ROLLBACK TRANSACTION
			SET @StrStatus = 'Unsucessfull';
			SET @Status = 0;
			select @Status intStatus, @StrStatus strStatus;
		RETURN;
		END

		IF @rows = 0
		BEGIN
			ROLLBACK TRANSACTION
			SET @StrStatus = 'UnSucessfull';
			SET @Status = 0;
			select @Status intStatus, @StrStatus strStatus;
			RETURN;
		END
		
COMMIT TRANSACTION
		select @Status intStatus, @StrStatus strStatus;
		return;
END
