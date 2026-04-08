USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_LeaveApplicationsUnlikely]    Script Date: 28/07/2025 16:40:25 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  PROCEDURE [dbo].[usp_mod_LeaveApplicationsUnlikely] 
	-- Add the parameters for the stored procedure test
	
	@setunlikely bit,
	@leaveapplicationid int,
	@history  Varchar(max),
	@modulename  Varchar(100),
	@currentuserid int
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @historytype int
	DECLARE @Status bit, @Returncode varchar(10), @err int, @rows int
--set value
	SET @Returncode = 'Success';
	SET @Status = 1;
BEGIN TRANSACTION
		UPDATE dbo.LeaveApplications
			SET  unlikely = @setunlikely
			WHERE  (ID = @leaveapplicationid)

			   SELECT @err = @@ERROR, @rows = @@ROWCOUNT
	    
				IF @err <> 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @Returncode = 'Error while update leave application ';
					SET @Status = 0;
					select @Status intStatus, @Returncode strStatus;
				RETURN;
				END
		
				IF @rows = 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @Returncode = 'Error while update leave application';
					SET @Status = 0;
					select @Status intStatus, @Returncode strStatus;
					RETURN;
				END

 	  
	--enter history

	Select @historytype = id from HistoryTypes where HistoryType = @modulename

	INSERT INTO [dbo].[History]([HistoryType],[UserID],[History],[datetime],[AttributeID])
		VALUES (@historytype,@currentuserid,@history,GETDATE(),@leaveapplicationid)
	
	COMMIT TRANSACTION
	select @Status intStatus, @Returncode strStatus;		
END
