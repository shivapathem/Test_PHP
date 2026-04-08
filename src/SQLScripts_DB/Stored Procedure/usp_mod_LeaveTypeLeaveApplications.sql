USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_LeaveTypeLeaveApplications]    Script Date: 2/10/2021 11:32:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_mod_LeaveTypeLeaveApplications]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE dbo.usp_mod_LeaveTypeLeaveApplications 
	-- Add the parameters for the stored procedure here
	
	@leavetype int,
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
	SET @Returncode = ''Success'';
	SET @Status = 1;
BEGIN TRANSACTION
		UPDATE dbo.LeaveApplications
			SET  LeaveTypesID = @leavetype
			WHERE  (ID = @leaveapplicationid)

				SELECT @err = @@ERROR, @rows = @@ROWCOUNT
	    
				IF @err <> 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @Returncode = ''Error while update leave application '';
					SET @Status = 0;
					select @Status intStatus, @Returncode strStatus;
				RETURN;
				END
		
				IF @rows = 0
				BEGIN
					ROLLBACK TRANSACTION
					SET @Returncode = ''Error while update leave application'';
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
'
EXEC dbo.sp_executesql @strSQL

GO
