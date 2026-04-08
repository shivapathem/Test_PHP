USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_del_Holiday]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_del_Holiday]
@id			INT,
@currentuserid	INT, 
@action		varchar(20),
@currentstatus int,
@status			INT OUTPUT,
@returnstring	VARCHAR(1000) OUTPUT

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @RowNum int
	DECLARE @UpdateDateTime datetime
	DECLARE @strStatus varchar(50)
	Declare @isdeleted int
	Declare @holidaydate date;
	
	SET @status = 1 
	SET @UpdateDateTime = GETDATE()
	
	IF @action = ''delete''
	BEGIN
		--Soft Delete publicholidays--
		SELECT  @holidaydate = Holidaydate FROM PublicHolidays WHERE PublicHolidayId =@id
			IF @holidaydate > getdate()
				BEGIN 
				update PublicHolidays  
						SET IsDeleted = 1,
							ModifiedBy = @currentuserid,
							ModifiedDate = @UpdateDateTime
						WHERE PublicHolidayId = @id

				SET @status = 1;
				SET @strStatus = ''Holiday deleted successfully'';
				END 
			ELSE
			  BEGIN
			  	SET @status = 0;
				SET @strStatus = ''You can not deleted past date holiday'';
			 END
 
	END

	IF @action = ''active''
	BEGIN
		--Soft Delete publicholidays--
		Declare @newStatus int;
		select 
			@newStatus = CASE WHEN IsActive = 0 THEN 1 
				ELSE 0 END
		from PublicHolidays where PublicHolidayId = @id

		update PublicHolidays  
				SET IsActive = @newStatus,
				    ModifiedBy = @currentuserid,
					ModifiedDate = @UpdateDateTime
				WHERE PublicHolidayId = @id
		SET @status = 1;
		if @newStatus = 0
		 SET @strStatus = ''Holiday has been deactivated successfully'';
		 if @newStatus = 1
		 SET @strStatus = ''Holiday has been activated successfully'';
		
	END


	select @status intStatus, @strStatus strStatus;
END
'

EXEC dbo.sp_executesql @strSQL

GO